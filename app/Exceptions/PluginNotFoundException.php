<?php

declare(strict_types=1);

namespace App\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\HTTPExceptionInterface;
use CodeIgniter\Exceptions\RuntimeException;

class PluginNotFoundException extends RuntimeException implements HTTPExceptionInterface
{
    use DebugTraceableTrait;

    /**
     * HTTP status code
     *
     * @var int
     */
    protected $code = 404;

    /**
     * @return static
     */
    public static function forPluginNotFound(string $pluginKey): self
    {
        return new static(self::lang('Plugin.exceptions.pluginNotFound', [
            'pluginKey' => $pluginKey,
        ]));
    }

    public static function forVersionNotFound(string $pluginKey, string $tag): self
    {
        return new static(self::lang('Plugin.exceptions.versionNotFound', [
            'pluginKey' => $pluginKey,
            'tag'       => $tag,
        ]));
    }

    /**
     * Get translated system message
     *
     * Use a non-shared Language instance in the Services.
     * If a shared instance is created, the Language will
     * have the current locale, so even if users call
     * `$this->request->setLocale()` in the controller afterwards,
     * the Language locale will not be changed.
     *
     * @param array<string,string> $args
     */
    private static function lang(string $line, array $args = []): string
    {
        $lang = service('language', null, false);

        /** @var string */
        return $lang->getLine($line, $args);
    }
}
