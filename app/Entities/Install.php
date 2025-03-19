<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property int $version_id
 * @property Time $date
 * @property int $count
 */
class Install extends BaseEntity
{
    protected $dates = ['date'];

    protected $casts = [];
}
