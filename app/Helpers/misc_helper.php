<?php

declare(strict_types=1);

use CodeIgniter\I18n\Time;

if (! function_exists('format_bytes')) {
    /**
     * Adapted from https://stackoverflow.com/a/2510459
     */
    function format_bytes(float $bytes, bool $is_binary = false, int $precision = 2): string
    {
        $units = $is_binary ? ['B', 'KiB', 'MiB', 'GiB', 'TiB'] : ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = (int) floor(($bytes ? log($bytes) : 0) / log($is_binary ? 1024 : 1000));
        $pow = min($pow, count($units) - 1);

        $bytes /= ($is_binary ? 1024 : 1000) ** $pow;

        return round($bytes, $precision) . $units[$pow];
    }
}

if (! function_exists('number_abbr')) {
    function number_abbr(int $number): string
    {
        if ($number < 1000) {
            return (string) $number;
        }

        $option = match (true) {
            $number < 1_000_000 => [
                'divider' => 1_000,
                'suffix'  => 'K',
            ],
            $number < 1_000_000_000 => [
                'divider' => 1_000_000,
                'suffix'  => 'M',
            ],
            default => [
                'divider' => 1_000_000_000,
                'suffix'  => 'B',
            ],
        };
        $formatter = new NumberFormatter(service('request')->getLocale(), NumberFormatter::DECIMAL);

        $formatter->setPattern('#,##0');

        $abbr = $formatter->format($number / $option['divider']) . $option['suffix'];

        $number = number_format($number);

        return <<<HTML
            <abbr title="{$number}">{$abbr}</abbr>
        HTML;
    }
}

if (! function_exists('number_format')) {
    function number_format(int $number): string
    {
        $formatter = new NumberFormatter(service('request')->getLocale(), NumberFormatter::DECIMAL);

        $formatter->setPattern('#,##0');

        $formattedNumber = $formatter->format($number);

        if (! $formattedNumber) {
            throw new RuntimeException(sprintf('Could not format number %s', $number));
        }

        return $formattedNumber;
    }
}

if (! function_exists('relative_time')) {
    function relative_time(Time $time, string $class = ''): string
    {
        $formatter = new IntlDateFormatter(service(
            'request',
        )->getLocale(), IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);

        $pattern = $formatter->getPattern();

        if (! $pattern) {
            throw new RuntimeException(sprintf('Could not get pattern for time %s', (string) $time));
        }

        $translatedDate = $time->toLocalizedString($pattern);
        $datetime = $time->format(DateTime::ATOM);

        return <<<HTML
            <relative-time tense="auto" class="{$class}" datetime="{$datetime}">
                <time
                    datetime="{$datetime}"
                    title="{$time}">{$translatedDate}</time>
            </relative-time>
        HTML;
    }
}

if (! function_exists('remove_category_from_uri')) {
    function remove_category_from_current_url(string $categoryToRemove): string
    {
        return (string) preg_replace(
            '/categories%5B\d+%5D\=' . $categoryToRemove . '&?/',
            '',
            (string) current_url(true),
        );
    }
}
