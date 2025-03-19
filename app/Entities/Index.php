<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property URI $repository_url
 * @property string $manifest_root
 * @property Time $submitted_at
 */
class Index extends BaseEntity
{
    protected $dates = ['submitted_at'];
}
