<?php

declare(strict_types=1);

namespace App\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;
use Exception;

class EnumCast extends BaseCast
{
    #[\Override]
    public static function get(mixed $value, array $params = [], ?object $helper = null): mixed
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        if ($params === []) {
            throw new Exception('Missing enum type parameter.');
        }

        $enumNS = sprintf('\App\Entities\Enums\%s', $params[0]);

        return $enumNS::tryFrom($value);
    }

    #[\Override]
    public static function set(mixed $value, array $params = [], ?object $helper = null): string
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        if ($params === []) {
            throw new Exception('Missing enum type parameter.');
        }

        $enumNS = sprintf('\App\Entities\Enums\%s', $params[0]);

        $enum = $enumNS::getFrom($value);

        return $enum->value;
    }
}
