<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\HTTP\URI;

/**
 * @property string $name
 * @property string $description
 * @property URI $repository_url
 * @property string $repository_folder
 * @property string $readme_markdown
 * @property URI $homepage
 * @property string $license
 * @property string $license_markdown
 * @property list<string> $categories
 * @property string $minCastopodVersion
 * @property list<string> $hooks
 */
class Plugin extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'repository_url' => 'uri',
        'homepage'       => 'uri',
    ];
}
