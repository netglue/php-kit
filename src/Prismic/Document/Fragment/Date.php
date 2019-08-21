<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Prismic\Exception\ExceptionInterface;
use Throwable;
use function preg_match;
use function Prismic\dateTimeImmutableFromFormat;

class Date extends AbstractScalarFragment
{

    /** @var string */
    private $format = 'c';

    private $hasTimePart = true;

    /** @var string|null */
    protected $value;

    public static function factory($value) : self
    {
        /** @var Date $fragment */
        $fragment = parent::factory($value);
        if (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', (string) $fragment->value)) {
            $fragment->format = 'Y-m-d';
            $fragment->hasTimePart = false;
        }
        return $fragment;
    }

    public function asDateTime() :? DateTimeInterface
    {
        $format = $this->hasTimePart ? DateTime::ATOM : '!Y-m-d';
        try {
            return dateTimeImmutableFromFormat($format, (string) $this->value);
        } catch (ExceptionInterface $exception) {
            return null;
        }
    }

    public function format() : string
    {
        return $this->format;
    }

    public function asHtml(?callable $serializer = null) :? string
    {
        if ($serializer) {
            return $serializer($this);
        }
        $date = $this->asDateTime();
        if ($date) {
            return sprintf(
                '<time datetime="%s">%s</time>',
                $date->format($this->format),
                $this->value
            );
        }
        return null;
    }
}
