<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

service('auth')
    ->routes($routes);

$routes->get('/', 'Plugins::index', [
    'as' => 'index',
]);
$routes->get('search', 'Plugins::index', [
    'as' => 'search',
]);
$routes->get('submit-plugin', 'Plugins::submit', [
    'as' => 'plugin-submit',
]);
$routes->post('/', 'Plugins::submitAction', [
    'as' => 'plugin-index',
]);
$routes->get('(:segment)/(:segment)', 'Plugins::info/$1/$2', [
    'as' => 'plugin-info',
]);
$routes->get('(:segment)/(:segment)/v/(:segment)', 'Plugins::info/$1/$2/$3', [
    'as' => 'plugin-version',
]);
