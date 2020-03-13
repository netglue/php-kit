<?php
declare(strict_types=1);

namespace Prismic\Exception;

use JsonException;
use function sprintf;

class JsonError extends JsonException implements ExceptionInterface
{
    /** @var string|null */
    private $payload;

    public static function unserializeFailed(JsonException $exception, string $payload) : self
    {
        $error = new static(
            sprintf(
                'Failed to decode JSON payload: %s',
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );

        $error->payload = $payload;

        return $error;
    }

    public function payload() :? string
    {
        return $this->payload;
    }
}
