<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Plugin;

class PluginModel extends BaseModel
{
    protected $table = 'plugins';

    protected $returnType = Plugin::class;

    protected $allowedFields = [
        'vendor',
        'name',
        'description',
        'icon_svg',
        'repository_url',
        'repository_folder',
        'homepage',
        'categories',
    ];

    protected array $casts = [
        'categories' => 'enum-array[Category]',
    ];

    // Dates
    protected $useTimestamps = true;

    // Validation
    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;
}
