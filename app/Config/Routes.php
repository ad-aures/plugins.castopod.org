<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->addPlaceholder('pluginKey', '[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9]([_.-]?[a-z0-9]+)*');

service('auth')
    ->routes($routes, [
        'except' => ['register'],
    ]);

$routes->get('register', '\App\Controllers\RegisterController::registerView');
$routes->post('register', '\App\Controllers\RegisterController::registerAction');

$routes->get('/', 'Plugins::index', [
    'as' => 'index',
]);
$routes->get('search', 'Plugins::index', [
    'as' => 'search',
]);
$routes->get('submit', 'Plugins::submit', [
    'as' => 'plugin-submit',
]);
$routes->post('/', 'Plugins::submitAction', [
    'as' => 'plugin-index',
]);
$routes->get('@(:pluginKey)', 'Plugins::info/$1', [
    'as' => 'plugin-info',
]);
$routes->get('@(:any)/v/(:segment)', 'Plugins::info/$1/$2', [
    'as' => 'plugin-version',
]);
$routes->post('@(:any)/v/(:segment)/download', 'Plugins::download/$1/$2', [
    'as' => 'plugin-version-download',
]);
$routes->get('my-plugins', 'Plugins::myPlugins', [
    'as' => 'my-plugins',
]);
$routes->post('@(:pluginKey)', 'Plugins::action/$1', [
    'as'     => 'plugin-action',
    'filter' => ['permission:plugins.manage,plugins$1.manage'],
]);
$routes->get('@(:any)/edit', 'Plugins::edit/$1', [
    'as'     => 'plugin-edit',
    'filter' => ['permission:plugins.manage,plugins$1.manage'],
]);

/**
 * API v1 ROUTES
 */
$routes->group('api/v1', static function (RouteCollection $routes) {
    $routes->get('health', 'API::health', [
        'as' => 'api-health',
    ]);

    $routes->get('(:pluginKey)', 'API::pluginInfo/$1', [
        'as' => 'api-plugin-info',
    ]);
    $routes->get('(:any)/v/latest', 'API::versionInfo/$1', [
        'as' => 'api-latest-version-info',
    ]);
    $routes->get('(:any)/v/(:segment)', 'API::versionInfo/$1/$2', [
        'as' => 'api-version-info',
    ]);

    $routes->get('(:any)/versions', 'API::pluginVersions/$1', [
        'as' => 'api-plugin-versions',
    ]);

    $routes->post('(:any)/v/(:segment)/downloads', 'API::incrementDownloads/$1/$2', [
        'as' => 'api-increment-downloads',
    ]);
});
