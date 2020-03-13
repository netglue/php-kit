<?php
declare(strict_types=1);

namespace Prismic;

use JsonException;
use Prismic\Exception\JsonError;
use function json_decode;
use const JSON_THROW_ON_ERROR;

final class Json
{
    /**
     * @throws JsonError If decoding the payload fails for any reason.
     */
    public static function decodeObject(string $jsonString) : object
    {
        try {
            return json_decode($jsonString, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw JsonError::unserializeFailed($exception, $jsonString);
        }
    }
}
