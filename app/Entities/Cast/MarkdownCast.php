<?php

declare(strict_types=1);

namespace App\Entities\Cast;

use App\Libraries\Markdown;
use CodeIgniter\Entity\Cast\BaseCast;

class MarkdownCast extends BaseCast
{
    /**
     * @param string $value
     * @param array<mixed> $params
     */
    #[\Override]
    public static function get($value, array $params = []): Markdown // @phpstan-ignore typeCoverage.paramTypeCoverage
    {
        return new Markdown($value);
    }

    /**
     * @param string $value
     * @param array<mixed> $params
     */
    #[\Override]
    public static function set($value, array $params = []): string // @phpstan-ignore typeCoverage.paramTypeCoverage
    {
        return $value;
    }
}
