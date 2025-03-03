<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * @property int $version_id
 * @property Time $date
 * @property int $count
 */
class Download extends Entity
{
    protected $dates = ['date'];

    protected $casts = [];
}
