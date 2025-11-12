<?php

declare(strict_types=1);

namespace App\Views\Decorators;

use CodeIgniter\View\ViewDecoratorInterface;

class CustomHeadScripts implements ViewDecoratorInterface
{
    public static function decorate(string $html): string
    {
        if (str_contains($html, 'viewDecoratorsEmailEnvironment')) {
            return $html;
        }

        $cacheName = 'custom-head-scripts';

        if (! $found = cache($cacheName)) {
            $found = (string) @file_get_contents(ROOTPATH . 'custom-head-scripts.html');

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var string $found */
        return str_replace('</head>', "\n\t{$found}\n</head>", $html);
    }
}
