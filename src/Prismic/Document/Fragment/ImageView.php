<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment\Link\AbstractLink;
use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;
use Prismic\Serializer\HtmlSerializer;
use Prismic\Serializer\Serializer;
use stdClass;
use function is_numeric;
use function is_object;
use function is_string;
use function sprintf;

class ImageView implements ImageInterface
{
    use HtmlHelperTrait;

    /** @var string */
    private $url;

    /** @var string|null */
    private $alt;

    /** @var string|null */
    private $copyright;

    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /** @var string|null */
    private $label;

    /** @var LinkInterface|null */
    private $link;

    private $linkResolver;

    private function __construct(LinkResolver $linkResolver)
    {
        $this->linkResolver = $linkResolver;
    }

    public static function factory($value, LinkResolver $linkResolver) : self
    {
        static::validatePayload($value);
        $image            = new static($linkResolver);
        $image->url       = $value->url;
        $image->alt       = $value->alt ?? null;
        $image->copyright = $value->copyright ?? null;
        $image->label     = $value->label ?? null;
        $image->width     = $value->dimensions->width;
        $image->height    = $value->dimensions->height;
        $image->link      = isset($value->linkTo)
                          ? AbstractLink::abstractFactory($value->linkTo, $linkResolver)
                          : null;
        return $image;
    }

    private static function validatePayload(stdClass $image) : void
    {
        if (! isset($image->url) || ! is_string($image->url)) {
            throw new InvalidArgumentException('The image payload must have a url property with a non-empty string');
        }
        if (! isset($image->dimensions) || ! is_object($image->dimensions)) {
            throw new InvalidArgumentException(
                'The image payload must have a dimensions property containing the width and height'
            );
        }
        if (! isset($image->dimensions->width) || ! is_numeric($image->dimensions->width)) {
            throw new InvalidArgumentException('The image payload must have a width property containing a number');
        }
        if (! isset($image->dimensions->height) || ! is_numeric($image->dimensions->height)) {
            throw new InvalidArgumentException('The image payload must have a height property containing a number');
        }
    }

    public function asText() :? string
    {
        return $this->url;
    }

    public function asHtml(?callable $serializer = null) :? string
    {
        $serializer = $serializer ?: new HtmlSerializer($this->linkResolver);
        return $serializer($this);
    }

    public function getLabel() :? string
    {
        return $this->label;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getAlt() :? string
    {
        return $this->alt;
    }

    public function getCopyright() :? string
    {
        return $this->copyright;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }

    public function getLink() :? LinkInterface
    {
        return $this->link;
    }

    public function hasLink() : bool
    {
        return $this->link !== null;
    }

    public function ratio() : float
    {
        return (float) ($this->width / $this->height);
    }

    public function serialize(Serializer $serializer) :? string
    {
        return $serializer->serialize($this);
    }
}
