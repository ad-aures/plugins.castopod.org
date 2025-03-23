<?php

declare(strict_types=1);

namespace App\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;
use CodeIgniter\DataCaster\Exceptions\CastException;
use Exception;
use JsonException;

class JsonArrayObjectCast extends BaseCast
{
    #[\Override]
    public static function get(mixed $value, array $params = [], ?object $helper = null): mixed
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        if ($params === []) {
            throw new Exception('Missing entity name parameter.');
        }

        $output = [];
        try {
            /** @var array<mixed> $output */
            $output = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw CastException::forInvalidJsonFormat($e->getCode());
        }

        $entity = sprintf('\App\Entities\%s', $params[0]);

        foreach ($output as $key => $element) {
            $output[$key] = new $entity($element);
        }

        return $output;
    }

    #[\Override]
    public static function set(mixed $value, array $params = [], ?object $helper = null): string
    {
        try {
            $output = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw CastException::forInvalidJsonFormat($e->getCode());
        }

        return $output;
    }
}
