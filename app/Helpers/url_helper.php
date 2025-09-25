<?php

declare(strict_types=1);

use CodeIgniter\HTTP\URI;
use Config\Media;

if (! function_exists('media_url')) {
    /**
     * Returns a media URL as defined by the Media config.
     *
     * @param array<string>|string $relativePath URI string or array of URI segments
     */
    function media_url(string $folder, array|string $relativePath = '', ?string $scheme = null): string
    {
        /** @var Media $mediaConfig */
        $mediaConfig = config('Media');

        if (! array_key_exists($folder, $mediaConfig->folders)) {
            throw new RuntimeException(sprintf('Media folder "%s" was not defined in Media config.', $folder));
        }

        // Convert array of segments to a string
        if (is_array($relativePath)) {
            $relativePath = implode('/', $relativePath);
        }

        $relativePath = $folder . '/' . $relativePath;

        $uri = new URI(rtrim($mediaConfig->baseURL, '/') . '/' . trim($mediaConfig->root, '/') . '/' . ltrim(
            $relativePath,
            '/',
        ));

        return URI::createURIString(
            $scheme ?? $uri->getScheme(),
            $uri->getAuthority(),
            $uri->getPath(),
            $uri->getQuery(),
            $uri->getFragment(),
        );
    }
}

if (! function_exists('media_path')) {
    /**
     * Returns the absolute media path as defined by the Media config.
     */
    function media_path(string $folder, string $path = ''): string
    {
        /** @var Media $mediaConfig */
        $mediaConfig = config('Media');

        if (! array_key_exists($folder, $mediaConfig->folders)) {
            throw new RuntimeException(sprintf('Media folder "%s" was not defined in Media config.', $folder));
        }

        return DIRECTORY_SEPARATOR . implode(
            DIRECTORY_SEPARATOR,
            [
                trim($mediaConfig->storage, DIRECTORY_SEPARATOR),
                trim($mediaConfig->root, DIRECTORY_SEPARATOR),
                trim($mediaConfig->folders[$folder], DIRECTORY_SEPARATOR),
                ltrim($path, DIRECTORY_SEPARATOR),
            ],
        );
    }
}
