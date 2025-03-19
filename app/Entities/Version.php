<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Enums\Hook;
use App\Entities\Enums\License;
use App\Libraries\Markdown;
use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property int $plugin_id
 * @property string $tag
 * @property string $commit
 * @property ?Markdown $readme_markdown
 * @property License $license
 * @property string $min_castopod_version
 * @property Hook[] $hooks
 * @property int $size
 * @property int $file_count
 * @property int $installs_total
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
        'installs_total'  => 'int',
    ];
}
