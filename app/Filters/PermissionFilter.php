<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Filters;

use CodeIgniter\Shield\Filters\PermissionFilter as ShieldPermissionFilter;
use RuntimeException;

/**
 * Permission Authorization Filter.
 */
class PermissionFilter extends ShieldPermissionFilter
{
    /**
     * Ensures the user is logged in and has one or more
     * of the permissions as specified in the filter.
     *
     * @param list<string> $arguments
     */
    #[\Override]
    protected function isAuthorized(array $arguments): bool
    {
        foreach ($arguments as $permission) {
            // is permission specific to a podcast?
            if (str_contains($permission, '$')) {
                $router = service('router');
                $routerParams = $router->params();
                if (! preg_match('/\$(\d+)\./', $permission, $match)) {
                    throw new RuntimeException(sprintf(
                        'Could not get pluginKey identifier from permission %s',
                        $permission,
                    ), 1);
                }
                $paramKey = ((int) $match[1]) - 1;
                if (! array_key_exists($paramKey, $routerParams)) {
                    throw new RuntimeException(sprintf('Router param does not exist at key %s', $match[1]), 1);
                }
                $pluginKey = $routerParams[$paramKey];
                if (is_user_maintainer_of($pluginKey)) {
                    return true;
                }
            } elseif (auth()->user()->can($permission)) { // @phpstan-ignore method.nonObject
                return true;
            }
        }

        return false;
    }
}
