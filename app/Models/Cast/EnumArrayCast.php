<?php

declare(strict_types=1);

namespace App\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;

class EnumArrayCast extends BaseCast
{
    #[\Override]
    public static function get(mixed $value, array $params = [], ?object $helper = null): mixed
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return explode(',', trim($value, '{}'));
    }

    #[\Override]
    public static function set(mixed $value, array $params = [], ?object $helper = null): string
    {
        if (! is_array($value)) {
            self::invalidTypeValueError($value);
        }

        return sprintf('{%s}', implode(',', $value));
    }
}
