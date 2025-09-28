<?php

declare(strict_types=1);

namespace Config;

use CodeIgniterAltcha\Config\Altcha as AltchaConfig;

class Altcha extends AltchaConfig
{
    public array $filterExcludedPaths = ['api/*'];
}
