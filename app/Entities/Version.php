<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property int $plugin_id
 * @property string $tag
 * @property string $commit
 * @property string $readme_markdown
 * @property string $license
 * @property string $license_markdown
 * @property string $min_castopod_version
 * @property list<string> $hooks
 * @property Time $published_at
 */
class Version extends BaseEntity
{
    protected $dates = ['published_at'];
}
