<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\I18n\Time;

/**
 * @property string $plugin_key
 * @property string $version_tag
 * @property Time $date
 * @property int $count
 */
class Download extends BaseEntity
{
    protected $dates = ['date'];

    protected $casts = [];
}
