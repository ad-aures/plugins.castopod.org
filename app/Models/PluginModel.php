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
        'vendor',
        'name',
        'description',
        'icon_svg',
        'repository_url',
        'manifest_root',
        'homepage_url',
        'categories',
        'authors',
        'installs_total',
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

    public function getPluginByName(string $vendor, string $name): Plugin
    {
        $cacheName = sprintf('plugin_%s_%s', $vendor, $name);

        if (! ($found = cache($cacheName))) {
            $found = $this->where([
                'vendor' => $vendor,
                'name'   => $name,
            ])->first();

            if (! $found instanceof Plugin) {
                throw PluginNotFoundException::forPluginNotFound($vendor, $name);
            }

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Plugin $found */
        return $found;
    }
}
