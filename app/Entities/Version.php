<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * @property int $plugin_id
 * @property string $tag
 * @property string $commit_hash
 * @property Time $published_at
 */
class Version extends Entity
{
    protected $dates = ['published_at'];

    protected $casts = [];
}
