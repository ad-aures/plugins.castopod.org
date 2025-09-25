<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Plugin;
use App\Exceptions\PluginNotFoundException;

class PluginModel extends BaseModel
{
    protected $table = 'plugins';

    protected $returnType = Plugin::class;

    protected $allowedFields = [
        'key',
        'description',
        'icon_svg',
        'repository_url',
        'manifest_root',
        'homepage_url',
        'categories',
        'authors',
        'downloads_total',
        'is_updating',
        'owner_id',
    ];

    protected array $casts = [
        'categories' => 'enum-array[Category]',
        'authors'    => 'json-array-object[Person]',
    ];

    // Dates
    protected $useTimestamps = true;

    // Validation
    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    /**
     * @var list<string>
     */
    protected $afterUpdate = ['clearCache'];

    public function getPluginByRepository(string $repositoryUrl, string $manifestRoot): Plugin
    {
        // TODO: add cache?

        $found = $this->where([
            'repository_url' => $repositoryUrl,
            'manifest_root'  => $manifestRoot,
        ])->first();

        if (! $found instanceof Plugin) {
            throw PluginNotFoundException::forPluginByRepositoryNotFound($repositoryUrl, $manifestRoot);
        }

        /** @var Plugin $found */
        return $found;
    }

    public function getPluginByKey(string $pluginKey): Plugin
    {
        $cacheName = sprintf('plugin#%s', str_replace('/', '_', $pluginKey));

        if (! ($found = cache($cacheName))) {
            $found = $this->where([
                'key' => $pluginKey,
            ])->first();

            if (! $found instanceof Plugin) {
                throw PluginNotFoundException::forPluginNotFound($pluginKey);
            }

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Plugin $found */
        return $found;
    }

    /**
     * @return Plugin[]
     */
    public function getUserPlugins(int $userId): array
    {
        $cacheName = sprintf('user#%d_plugins', $userId);

        if (! ($found = cache($cacheName))) {
            $found = $this->select('plugins.*')
                ->join('plugins_maintainers', 'plugins_maintainers.plugin_key = plugins.key', 'left')
                ->where([
                    'owner_id' => $userId,
                ])->orWhere('user_id', $userId)
                ->groupBy('plugins.id')
                ->findAll();

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Plugin[] $found */
        return $found;
    }

    public function setUpdating(int $pluginId, bool $isUpdating): bool
    {
        // prevent concurrent updates by ensuring that is_updating is getting changed
        return $this->set('is_updating', $isUpdating)
            ->where('is_updating', ! $isUpdating)
            ->update($pluginId);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function clearCache(array $data): array
    {
        /** @var int|null $pluginId */
        $pluginId = is_array($data['id']) ? $data['id'][0] : $data['id'];

        if ($pluginId === null) {
            // Multiple plugins have been updated, do nothing
            return $data;
        }

        /** @var ?Plugin $plugin */
        $plugin = new self()
            ->find($pluginId);

        if (! $plugin instanceof Plugin) {
            return $data;
        }

        cache()
            ->deleteMatching(sprintf('plugin#%s*', str_replace('/', '_', $plugin->key)));
        cache()
            ->delete(sprintf('user#%d_plugins', $plugin->owner_id));

        return $data;
    }
}
