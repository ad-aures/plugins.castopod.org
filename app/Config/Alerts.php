<?php

declare(strict_types=1);

namespace Config;

use Tatter\Alerts\Config\Alerts as TatterAlerts;

class Alerts extends TatterAlerts
{
    /**
     * Template to use for HTML output.
     */
    public string $template = '_alerts';
}
