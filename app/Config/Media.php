<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Media extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Media Base URL
     * --------------------------------------------------------------------------
     *
     * URL to your media root. Typically this will be your base URL,
     * WITH a trailing slash:
     *
     *    http://cdn.example.com/
     */
    public string $baseURL = 'http://localhost:8080/';

    /**
     * --------------------------------------------------------------------------
     * Media root folder
     * --------------------------------------------------------------------------
     * Defines the root folder for media files storage
     */
    public string $root = 'static';

    /**
     * --------------------------------------------------------------------------
     * Media storage folder
     * --------------------------------------------------------------------------
     * Defines the folder used to store the media root folder
     */
    public string $storage = ROOTPATH . 'public';

    /**
     * @var array<string, string>
     */
    public array $folders = [
        'plugins' => 'plugins',
        'avatars' => 'avatars',
    ];
}
