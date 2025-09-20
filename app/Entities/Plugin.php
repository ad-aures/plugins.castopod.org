<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Enums\Category;
use App\Models\UserModel;
use App\Models\VersionModel;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property string $key
 * @property string $vendor
 * @property string $name
 * @property ?string $description
 * @property ?string $icon_svg
 * @property URI $repository_url
 * @property string $manifest_root
 * @property ?URI $homepage_url
 * @property Category[] $categories
 * @property Person[] $authors
 * @property int $downloads_total
 * @property bool $is_updating
 * @property int $owner_id
 * @property User $owner
 *
 * @property Version[] $versions
 * @property list<string> $all_tags
 *
 * @property Version $latest_version
 *
 * @property ?string $selected_version_tag
 * @property Version $selected_version
 *
 * @property User[] $maintainers
 *
 * @property Time $created_at
 * @property Time $updated_at
 */
class Plugin extends BaseEntity
{
    public string $vendor;

    public string $name;

    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'id'             => 'int',
        'description'    => '?string-escaped',
        'repository_url' => 'uri',
        'homepage_url'   => '?uri',
        'is_updating'    => 'boolean',
        'owner_id'       => 'int',
    ];

    /**
     * @var Version[]|null
     */
    protected ?array $versions = null;

    /**
     * @var list<string>
     */
    protected ?array $all_tags = null;

    protected ?string $selected_version_tag = null;

    protected ?Version $selected_version = null;

    protected ?Version $latest_version = null;

    protected ?User $owner = null;

    /**
     * @var User[]
     */
    protected ?array $maintainers = null;

    /**
     * @param array<string, string> $data
     */
    #[\Override]
    public function injectRawData(array $data): self
    {
        parent::injectRawData($data);

        assert(is_string($this->attributes['key']));

        [$vendor, $name] = explode('/', $this->attributes['key']);

        $this->vendor = $vendor;
        $this->name = $name;

        return $this;
    }

    /**
     * @return Version[]
     */
    public function getVersions(): array
    {
        if ($this->versions === null) {
            $this->versions = new VersionModel()
                ->getAllPluginVersions($this->key);
        }

        return $this->versions;
    }

    /**
     * @return list<string>
     */
    public function getAllTags(): array
    {
        if ($this->all_tags === null) {
            $this->all_tags = [];
            foreach ($this->getVersions() as $version) {
                $this->all_tags[] = $version->tag;
            }
        }

        return $this->all_tags;
    }

    public function getLatestVersion(): Version
    {
        if ($this->latest_version === null) {
            $this->latest_version = new VersionModel()
                ->getLatestPluginVersion($this->key);
        }

        return $this->latest_version;
    }

    public function setSelectedVersionTag(?string $tag = null): self
    {
        $this->selected_version_tag = $tag;
        $this->selected_version = null; // @phpstan-ignore assign.propertyType

        return $this;
    }

    public function getSelectedVersion(): Version
    {
        if ($this->selected_version === null) {
            $this->selected_version = $this->selected_version_tag === null ? $this->getLatestVersion() : new VersionModel()
                ->getPluginVersion($this->key, $this->selected_version_tag);
        }

        return $this->selected_version;
    }

    public function getOwner(): User
    {
        if ($this->owner === null) {
            $this->owner = new UserModel()
                ->getPluginOwner($this->key);
        }

        return $this->owner;
    }

    /**
     * @return User[]
     */
    public function getMaintainers(): array
    {
        if ($this->maintainers === null) {
            $this->maintainers = new UserModel()
                ->getPluginMaintainers($this->key);
        }

        return $this->maintainers;
    }

    /**
     * @return array{vendor:string,name:string,description:?string,repository_url:string,manifest_root:string,homepage_url:?string,categories:Category[],authors:Person[],created_at:string,updated_at:string}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'key'            => $this->vendor . '/' . $this->name,
            'vendor'         => $this->vendor,
            'name'           => $this->name,
            'description'    => $this->description ? html_entity_decode($this->description) : null,
            'repository_url' => (string) $this->repository_url,
            'manifest_root'  => $this->manifest_root,
            'homepage_url'   => $this->homepage_url === null ? null : (string) $this->homepage_url,
            'categories'     => $this->categories,
            'authors'        => $this->authors,
            'created_at'     => $this->created_at->format(DATE_ATOM),
            'updated_at'     => $this->updated_at->format(DATE_ATOM),
        ];
    }
}
