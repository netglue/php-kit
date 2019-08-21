<?php
declare(strict_types=1);
namespace Prismic {

    use DateTimeImmutable;
    use DateTimeZone;
    use Throwable;
    use function sprintf;

    function dateTimeImmutableFromFormat(
        string $format,
        string $value,
        ?DateTimeZone $timezone = null
    ) : DateTimeImmutable {
        try {
            $date = DateTimeImmutable::createFromFormat($format, $value, $timezone);
            if (! $date instanceof DateTimeImmutable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'An invalid date format or value was provided - Format: "%s", Value: "%s"',
                    $format,
                    $value
                ));
            }
            return $date;
        } catch (Throwable $exception) {
            throw new Exception\InvalidArgumentException('Failed to create a DateTime instance', 500, $exception);
        }
    }
}
