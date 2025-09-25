<?php

declare(strict_types=1);

if (! function_exists('tempdir')) {
    /**
     * Creates a random unique temporary directory, with specified parameters,
     * that does not already exist (like tempnam(), but for dirs).
     *
     * Created dir will begin with the specified prefix, followed by random
     * numbers.
     *
     * @link https://php.net/manual/en/function.tempnam.php
     * @link adapted from https://stackoverflow.com/a/30010928
     *
     * @param string|null $dir Base directory under which to create temp dir.
     *     If null, the default system temp dir (sys_get_temp_dir()) will be
     *     used.
     * @param string $prefix String with which to prefix created dirs.
     * @param int $mode Octal file permission mask for the newly-created dir.
     *     Should begin with a 0.
     * @param int $maxAttempts Maximum attempts before giving up (to prevent
     *     endless loops).
     * @return string|false Full path to newly-created dir, or false on failure.
     */
    function tempdir(
        string $prefix = 'tmp_',
        ?string $dir = null,
        int $mode = 0700,
        int $maxAttempts = 1000,
    ): string|false {
        /* Use writable/temp dir by default. */
        if ($dir === null) {
            $dir = WRITEPATH . 'temp';
        }

        /* Trim trailing slashes from $dir. */
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        /* If we don't have permission to create a directory, fail, otherwise we will
         * be stuck in an endless loop.
         */
        if (! is_dir($dir) || ! is_writable($dir)) {
            return false;
        }

        /* Make sure characters in prefix are safe. */
        if (strpbrk($prefix, '\\/:*?"<>|') !== false) {
            return false;
        }

        /* Attempt to create a random directory until it works. Abort if we reach
         * $maxAttempts. Something screwy could be happening with the filesystem
         * and our loop could otherwise become endless.
         */
        $attempts = 0;
        do {
            $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, $prefix, mt_rand(100000, mt_getrandmax()));
        } while (
            ! mkdir($path, $mode) &&
            $attempts++ < $maxAttempts
        );

        return $path;
    }
}

if (! function_exists('delete_folder')) {
    function delete_directory(string $folderPath): bool
    {
        $deleteFiles = delete_files($folderPath, true, false, true);

        if (! $deleteFiles) {
            return false;
        }

        return @rmdir($folderPath);
    }
}

if (! function_exists('delete_path')) {
    /**
     * A recursive function that deletes a file and parent folders until one is not empty or until reaching root path.
     */
    function delete_path(string $path, string $rootPath): bool
    {
        // normalize paths using realpath
        $path = (string) realpath($path);
        $rootPath = (string) realpath($rootPath);

        if (file_exists($path) && ! is_dir($path)) {
            if (! unlink($path)) {
                return false;
            }

            return delete_path(dirname($path, 1), $rootPath);
        }

        if ($path === $rootPath) {
            // stop if $path is $rootPath
            return true;
        }

        $isDirEmpty = ! new \FilesystemIterator($path)
            ->valid();
        if ($isDirEmpty) {
            if (! delete_directory($path)) {
                return false;
            }

            return delete_path(dirname($path, 1), $rootPath);
        }

        return true;
    }
}
