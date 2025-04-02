<?php

declare(strict_types=1);

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BooleanCast as BaseBooleanCast;

class BooleanCast extends BaseBooleanCast
{
    /**
     * @param string $value
     * @param array<mixed> $params
     */
    #[\Override]
    public static function get($value, array $params = []): bool // @phpstan-ignore typeCoverage.paramTypeCoverage
    {
        if ($value === 't') {
            return true;
        }

        if ($value === 'f') {
            return false;
        }

        return parent::get($value, $params);
    }
}
