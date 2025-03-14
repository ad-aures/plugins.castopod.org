<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Version;

class VersionModel extends BaseModel
{
    protected $table = 'versions';

    protected $returnType = Version::class;

    protected $allowedFields = [
        'plugin_id',
        'tag',
        'commit',
        'readme_markdown',
        'license',
        'license_markdown',
        'min_castopod_version',
        'hooks',
        'published_at',
    ];

    protected array $casts = [
        'license' => 'enum[License]',
        'hooks'   => 'enum-array[Hook]',
    ];
}
