<?php

declare(strict_types=1);

namespace App\Views\Decorators;

use CodeIgniter\HTTP\URI;
use CodeIgniter\View\ViewDecoratorInterface;
use Config\App;

class HrefLang implements ViewDecoratorInterface
{
    public static function decorate(string $html): string
    {
        $hrefLangTags = '';

        // add x-default tag
        $hrefLangTags .= '<link
            rel="alternate"
            hreflang="x-default"
            href="' . base_url() . '"
        />';

        /** @var URI $currentUrl */
        $currentUrl = current_url(true);
        $href = clone $currentUrl;
        foreach (config(App::class)->supportedLocales as $supportedLocale) {
            $href->setSegment(1, $supportedLocale);

            $hrefLangTags .= '<link
                rel="alternate"
                hreflang="' . $supportedLocale . '"
                href="' . $href . '"
            />';
        }

        // free up memory
        unset($href);

        return str_replace('</head>', "\n\t{$hrefLangTags}\n</head>", $html);
    }
}
