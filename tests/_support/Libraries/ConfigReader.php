<?php

declare(strict_types=1);

namespace Tests\Support\Libraries;

/**
 * An extension of BaseConfig that prevents the constructor from
 * loading external values. Used to read actual local values from
 * a config file.
 */
class ConfigReader extends \Config\App
{
    public function __construct()
    {
    }
}
