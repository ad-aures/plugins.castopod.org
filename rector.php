<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/app', __DIR__ . '/tests'])
    ->withPhpSets(php84: true)
    ->withSkip([
        RemoveAlwaysTrueIfConditionRector::class => [__DIR__ . '/app/Config/Events.php'],
    ])
    ->withPreparedSets(deadCode: true, codeQuality: true);
