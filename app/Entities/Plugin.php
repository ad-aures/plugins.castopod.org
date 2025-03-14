<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property string $vendor
 * @property string $name
 * @property string $description
 * @property string $icon_svg
 * @property URI $repository_url
 * @property string $repository_folder
 * @property URI $homepage
 * @property list<string> $categories
 *
 * @property Time $created_at
 * @property Time $updated_at
 */
class Plugin extends BaseEntity
{
    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'description'    => 'string-escaped',
        'repository_url' => 'uri',
        'homepage'       => 'uri',
    ];
}
