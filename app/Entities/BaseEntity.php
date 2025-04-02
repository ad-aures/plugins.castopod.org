<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Cast\BooleanCast;
use App\Entities\Cast\MarkdownCast;
use App\Entities\Cast\StringEscapedCast;
use CodeIgniter\Entity\Entity;

class BaseEntity extends Entity
{
    protected $castHandlers = [
        'string-escaped' => StringEscapedCast::class,
        'markdown'       => MarkdownCast::class,
        'boolean'        => BooleanCast::class,
    ];
}
