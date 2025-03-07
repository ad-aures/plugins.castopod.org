<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->addPlaceholder('vendor', '[a-z0-9]([_.-]?[a-z0-9]+)*');
$routes->addPlaceholder('name', '[a-z0-9]([_.-]?[a-z0-9]+)*');

service('auth')
    ->routes($routes);

$routes->get('/', 'Plugins::index');
$routes->get('search', 'Plugins::index');
