<?php

declare(strict_types=1);

namespace Config;

use App\Jobs\CrawlPlugin;
use App\Jobs\UpdatePlugin;
use CodeIgniter\Queue\Config\Queue as BaseQueue;

class Queue extends BaseQueue
{
    public array $jobHandlers = [
        'plugin-crawl'  => CrawlPlugin::class,
        'plugin-update' => UpdatePlugin::class,
    ];
}
