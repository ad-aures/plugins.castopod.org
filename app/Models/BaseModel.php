<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Cast\EnumArrayCast;
use App\Models\Cast\EnumCast;
use App\Models\Cast\JsonArrayObjectCast;
use CodeIgniter\Model;

class BaseModel extends Model
{
    protected array $castHandlers = [
        'enum-array'        => EnumArrayCast::class,
        'enum'              => EnumCast::class,
        'json-array-object' => JsonArrayObjectCast::class,
    ];
}
