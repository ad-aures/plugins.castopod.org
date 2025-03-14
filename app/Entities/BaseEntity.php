<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Cast\StringEscaped;
use CodeIgniter\Entity\Entity;

class BaseEntity extends Entity
{
    protected $castHandlers = [
        'string-escaped' => StringEscaped::class,
    ];
}
