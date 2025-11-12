<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum License: string
{
    case AGPL3Only = 'AGPL-3.0-only';
    case AGPL3OrLater = 'AGPL-3.0-or-later';
    case Apache2 = 'Apache-2.0';
    case BSL1 = 'BSL-1.0';
    case Custom = 'Custom';
    case GPL3Only = 'GPL-3.0-only';
    case GPL3OrLater = 'GPL-3.0-or-later';
    case LGPL3Only = 'LGPL-3.0-only';
    case LGPL3OrLater = 'LGPL-3.0-or-later';
    case MIT = 'MIT';
    case MPL2 = 'MPL-2.0';
    case Unlicense = 'Unlicense';
    case UNLICENSED = 'UNLICENSED';

    public static function getFrom(string $value): self
    {
        if ($value === '') {
            return self::UNLICENSED;
        }

        $availableLicenses = License::values();
        $key = array_search(strtolower($value), array_map(strtolower(...), $availableLicenses), true);

        if (! $key) {
            return self::Custom;
        }

        return self::tryFrom($availableLicenses[$key]);
    }

    /**
     * @return list<value-of<self>>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
