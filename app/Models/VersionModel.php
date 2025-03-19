<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Plugin;
use App\Entities\Version;
use App\Exceptions\PluginNotFoundException;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\RawSql;

class VersionModel extends BaseModel
{
    protected $table = 'versions';

    protected $returnType = Version::class;

    protected $allowedFields = [
        'plugin_id',
        'tag',
        'commit',
        'readme_markdown',
        'license',
        'min_castopod_version',
        'hooks',
        'size',
        'file_count',
        'installs_total',
        'published_at',
    ];

    protected array $casts = [
        'license' => 'enum[License]',
        'hooks'   => 'enum-array[Hook]',
    ];

    /**
     * @return Version[]
     */
    public function getAllPluginVersions(int $pluginId): array
    {
        $cacheName = sprintf('plugin#%d-versions', $pluginId);

        if (! ($found = cache($cacheName))) {
            $found = $this->where([
                'plugin_id' => $pluginId,
            ])->orderBy('published_at', 'DESC')
                ->findAll();

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Version[] $found */
        return $found;
    }

    public function getLatestPluginVersion(Plugin $plugin): Version
    {
        $cacheName = sprintf('plugin#%d-latest_version', $plugin->id);

        if (! ($found = cache($cacheName))) {
            // SELECT *
            // FROM versions v
            // WHERE plugin_id = $pluginId
            // AND published_at = (
            //     SELECT COALESCE(
            //         (SELECT MAX(published_at) FROM versions WHERE plugin_id = $pluginId AND tag NOT LIKE 'dev-%'),
            //         (SELECT MAX(published_at) FROM versions WHERE plugin_id = $pluginId)
            //     )
            // )
            $found = $this->where([
                'plugin_id' => $plugin->id,
            ])->where('published_at', static function (BaseBuilder $builder) use ($plugin) {
                $db = db_connect();
                $subquery = $db->table('versions')
                    ->selectMax('published_at')
                    ->where([
                        'plugin_id'    => $plugin->id,
                        'tag NOT LIKE' => 'dev-%',
                    ]);
                $subquery2 = $db->table('versions')
                    ->selectMax('published_at')
                    ->where('plugin_id', $plugin->id);
                $rawQuery = sprintf(
                    'COALESCE((%s), (%s))',
                    $subquery->getCompiledSelect(),
                    $subquery2->getCompiledSelect(),
                );
                $builder->select(new RawSql($rawQuery));
            })->first();

            if (! $found instanceof Version) {
                throw PluginNotFoundException::forVersionNotFound($plugin->key, 'latest');
            }

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Version $found */
        return $found;
    }

    public function getPluginVersion(Plugin $plugin, string $tag): Version
    {
        $cacheName = sprintf('plugin#%d-version#%s', $plugin->id, $tag);

        if (! ($found = cache($cacheName))) {
            $found = $this->where([
                'plugin_id' => $plugin->id,
                'tag'       => $tag,
            ])->first();

            if (! $found instanceof Version) {
                throw PluginNotFoundException::forVersionNotFound($plugin->key, $tag);
            }

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Version $found */
        return $found;
    }
}
