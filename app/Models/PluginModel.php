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
}
