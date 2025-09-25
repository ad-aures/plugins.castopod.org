<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Enums\Hook;
use App\Entities\Enums\License;
use App\Libraries\Markdown;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;

/**
 * @property string $plugin_key
 * @property string $tag
 * @property string $commit_hash
 * @property ?Markdown $readme_markdown
 * @property License $license
 * @property string $min_castopod_version
 * @property Hook[] $hooks
 * @property int $size
 * @property int $file_count
 * @property string $archive_path
 * @property string $archive_checksum
 * @property URI $archive_url
 * @property int $downloads_total
 * @property Time $published_at
 */
class Version extends BaseEntity
{
    protected $dates = ['published_at'];

    protected $casts = [
        'id'              => 'int',
        'readme_markdown' => '?markdown',
        'size'            => 'int',
        'file_count'      => 'int',
        'downloads_total' => 'int',
    ];

    /**
     * @return array{tag:string,commit_hash:string,readme:?string,license:value-of<License>,min_castopod_version:string,hooks:Hook[],size:int,file_count:int,archive:array{url:string,checksum:string},published_at:string}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'tag'                  => $this->tag,
            'commit_hash'          => $this->commit_hash,
            'readme'               => $this->readme_markdown === null ? null : (string) $this->readme_markdown,
            'license'              => $this->license->value,
            'min_castopod_version' => $this->min_castopod_version,
            'hooks'                => $this->hooks,
            'size'                 => $this->size,
            'file_count'           => $this->file_count,
            'archive'              => [
                'url'      => (string) $this->archive_url,
                'checksum' => $this->archive_checksum,
            ],
            'published_at' => $this->published_at->format(DATE_ATOM),
        ];
    }

    public function getArchiveUrl(): URI
    {
        return new URI(media_url('plugins', $this->archive_path));
    }
}
