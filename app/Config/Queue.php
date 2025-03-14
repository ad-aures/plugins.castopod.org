<?php

declare(strict_types=1);

namespace Config;

use App\Jobs\CrawlPlugin;
use CodeIgniter\Queue\Config\Queue as BaseQueue;

class Queue extends BaseQueue
{
    public array $jobHandlers = [
        'crawl-plugin' => CrawlPlugin::class,
    ];
}
