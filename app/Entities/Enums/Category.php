<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum Category: string
{
    case Accessibility = 'accessibility';
    case Analytics = 'analytics';
    case Monetization = 'monetization';
    case Official = 'official';
    case Podcasting2 = 'podcasting2';
    case Privacy = 'privacy';
    case Productivity = 'productivity';
    case SEO = 'seo';

    /**
     * @param list<string> $value
     * @return self[]
     */
    public static function getFromArray(array $value): array
    {
        $enumCases = [];
        foreach ($value as $stringCategory) {
            $enumCase = self::tryFrom($stringCategory);

            if (! $enumCase instanceof self) {
                continue;
            }

            $enumCases[] = $enumCase;
        }

        return $enumCases;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (Category $case) => $case->value, self::cases());
    }
}
