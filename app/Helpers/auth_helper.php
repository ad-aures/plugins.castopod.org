<?php

declare(strict_types=1);

use CodeIgniter\Shield\Entities\User;
use Config\App;

if (! function_exists('is_user_maintainer_of')) {
    function is_user_maintainer_of(string $pluginKey): bool
    {
        if (! auth()->loggedIn()) {
            return false;
        }

        /**
         * SELECT COUNT(*)
         * FROM (
         *     SELECT *
         *     FROM (
         *         SELECT "key" as "plugin_key", "owner_id" as "user_id"
         *         FROM "plugins"
         *         WHERE "key" = $pluginKey
         *         AND "owner_id" = $userId
         *     ) "uwrp0"
         *     UNION
         *     SELECT *
         *     FROM (
         *         SELECT "plugin_key", "user_id"
         *         FROM "plugins_maintainers"
         *         WHERE "plugin_key" = $pluginKey
         *         AND "user_id" = $userId
         *     ) "uwrp1"
         * ) "maintainers"
         */
        $db = db_connect();

        $union = $db->table('plugins_maintainers')
            ->select('plugin_key, user_id')
            ->where([
                'plugin_key' => $pluginKey,
                'user_id'    => user_id(),
            ]);
        $builder = $db->table('plugins')
            ->select('key as plugin_key, owner_id as user_id')
            ->where([
                'key'      => $pluginKey,
                'owner_id' => user_id(),
            ])->union($union);

        return $db->newQuery()
            ->fromSubquery($builder, 'maintainers')
            ->countAllResults() !== 0;
    }
}

if (! function_exists('save_gravatar')) {
    function save_gravatar(User $user): string|false
    {
        helper('text');

        $extension = 'jpg';
        $baseGravatar = file_get_contents(
            sprintf(
                'https://gravatar.com/avatar/%s.%s?s=48&d=retro',
                hash('sha256', strtolower((string) $user->email)),
                $extension,
            ),
        );

        $fileHash = hash('md5', (string) $baseGravatar);

        $avatarPath = implode(
            DIRECTORY_SEPARATOR,
            [substr($fileHash, 0, 2), substr($fileHash, 2, 2), substr($fileHash, 4, 2), $fileHash],
        );

        /** @var App $appConfig */
        $appConfig = config('App');
        $avatarRelativePath = implode(
            DIRECTORY_SEPARATOR,
            [rtrim($appConfig->mediaRootPath, '/'), trim($appConfig->avatarsFolder, '/'), $avatarPath],
        );

        if (! is_dir(dirname($avatarRelativePath)) && ! @mkdir(dirname($avatarRelativePath), 0755, true)) {
            return false;
        }

        $path = sprintf('%s.%s', $avatarRelativePath, $extension);
        if (! file_put_contents($path, $baseGravatar)) {
            return false;
        }

        // save alternative sizes
        $altSizes = [
            'tiny' => 24,
        ];
        foreach ($altSizes as $name => $dimension) {
            $gravatar = file_get_contents(
                sprintf(
                    'https://gravatar.com/avatar/%s.%s?s=%d&d=retro',
                    hash('sha256', strtolower((string) $user->email)),
                    $extension,
                    $dimension,
                ),
            );

            $path = sprintf('%s-%s.%s', $avatarRelativePath, $name, $extension);
            if (! file_put_contents($path, $gravatar)) {
                return false;
            }
        }

        return sprintf('/%s.%s', $avatarPath, $extension);
    }
}
