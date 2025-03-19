<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Enums\Category;
use App\Models\VersionModel;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property string $vendor
 * @property string $name
 * @property string $key
 * @property ?string $description
 * @property ?string $icon_svg
 * @property URI $repository_url
 * @property string $manifest_root
 * @property ?URI $homepage_url
 * @property Category[] $categories
 * @property Person[] $authors
 * @property int $installs_total
 *
 * @property Version[] $versions
 * @property Version $latest_version
 *
 * @property Time $created_at
 * @property Time $updated_at
 */
class Plugin extends BaseEntity
{
    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'id'             => 'int',
        'description'    => '?string-escaped',
        'repository_url' => 'uri',
        'homepage_url'   => '?uri',
    ];

    /**
     * @var Version[]|null
     */
    protected ?array $versions = null;

    protected ?Version $latest_version = null;

    public function getKey(): string
    {
        return $this->vendor . '/' . $this->name;
    }

    /**
     * @return Version[]
     */
    public function getVersions(): array
    {
        if ($this->versions === null) {
            $this->versions = new VersionModel()
                ->getAllPluginVersions($this->id);
        }

        return $this->versions;
    }

    public function getLatestVersion(): Version
    {
        if ($this->latest_version === null) {
            $this->latest_version = new VersionModel()
                ->getLatestPluginVersion($this);
        }

        return $this->latest_version;
    }
}
