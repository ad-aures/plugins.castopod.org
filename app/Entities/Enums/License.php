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

        $enumCase = self::tryFrom($value);

        if ($enumCase instanceof self) {
            return $enumCase;
        }

        return self::Custom;
    }
}
