<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum Hook: string
{
    case RssBeforeChannel = 'rssBeforeChannel';
    case RssAfterChannel = 'rssAfterChannel';
    case RssBeforeItem = 'rssBeforeItem';
    case RssAfterItem = 'rssAfterItem';
    case SiteHead = 'siteHead';

    /**
     * @param list<string> $value
     * @return self[]
     */
    public static function getFromArray(array $value): array
    {
        $enumCases = [];
        foreach ($value as $stringHook) {
            $enumCase = self::tryFrom($stringHook);

            if (! $enumCase instanceof self) {
                continue;
            }

            $enumCases[] = $enumCase;
        }

        return $enumCases;
    }
}
