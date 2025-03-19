<?php

declare(strict_types=1);

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class StringEscapedCast extends BaseCast
{
    /**
     * @param string $value
     * @param array<mixed> $params
     */
    #[\Override]
    public static function get($value, array $params = []): string // @phpstan-ignore typeCoverage.paramTypeCoverage
    {
        return htmlspecialchars($value);
    }

    /**
     * @param string $value
     * @param array<mixed> $params
     */
    #[\Override]
    public static function set($value, array $params = []): string // @phpstan-ignore typeCoverage.paramTypeCoverage
    {
        return htmlspecialchars_decode($value);
    }
}
