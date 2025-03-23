<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Enums\Hook;
use App\Entities\Enums\License;
use App\Libraries\Markdown;
use CodeIgniter\I18n\Time;

/**
 * @property string $plugin_key
 * @property string $tag
 * @property string $commit_hash
 * @property ?Markdown $readme_markdown
 * @property License $license
 * @property string $min_castopod_version
 * @property list<Hook> $hooks
 * @property int $size
 * @property int $file_count
 * @property int $downloads_total
 * @property Time $published_at
 */
class Version extends BaseEntity
{
    protected $dates = ['published_at'];

    protected $casts = [
        'id'              => 'int',
        'plugin_id'       => 'int',
        'readme_markdown' => '?markdown',
        'size'            => 'int',
        'file_count'      => 'int',
        'downloads_total' => 'int',
    ];

    /**
     * @return array{tag:string,commit_hash:string,readme:?string,license:value-of<License>,min_castopod_version:string,hooks:list<Hook>,size:int,file_count:int,published_at:string}
     */
    #[\Override]
    public function jsonSerialize(): mixed
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
            'published_at'         => $this->published_at->format(DATE_ATOM),
        ];
    }
}
