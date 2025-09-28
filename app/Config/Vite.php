<?php

declare(strict_types=1);

namespace Config;

use CodeIgniterVite\Config\Vite as ViteConfig;

class Vite extends ViteConfig
{
    public array $routesAssets = [
        [
            'routes' => ['*'],
            'assets' => ['styles/index.css', 'js/index.ts'],
        ],
        [
            'routes' => ['/submit', '/login', '/register'],
            'assets' => ['js/altcha.ts'],
        ],
    ];
}
