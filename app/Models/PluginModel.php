<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Plugin;
use App\Models\Cast\EnumArrayCast;
use CodeIgniter\Model;

class PluginModel extends Model
{
    protected $table = 'plugins';

    protected $returnType = Plugin::class;

    protected $allowedFields = [
        'name',
        'description',
        'repository_url',
        'repository_folder',
        'readme_markdown',
        'homepage',
        'license',
        'license_markdown',
        'categories',
        'minCastopodVersion',
        'hooks',
    ];

    protected array $casts = [
        'categories' => 'enum-array',
        'hooks'      => 'enum-array',
    ];

    protected array $castHandlers = [
        'enum-array' => EnumArrayCast::class,
    ];

    // Dates
    protected $useTimestamps = true;

    // Validation
    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;
}
