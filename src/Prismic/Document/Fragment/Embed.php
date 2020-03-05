<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\Serializer\Serializer;
use stdClass;
use function array_diff_key;
use function array_flip;
use function json_encode;
use function sprintf;
use function strtolower;

class Embed implements FragmentInterface
{
    use HtmlHelperTrait;

    /** @var string|null */
    private $provider;

    /** @var string|null */
    private $html;

    /** @var string */
    private $type;

    /** @var string */
    private $url;

    /** @var int|null */
    private $width;

    /** @var int|null */
    private $height;

    /** @var string[] */
    private $attributes;

    private function __construct(
        string $type,
        string $url,
        array $attributes,
        ?string $html,
        ?string $provider,
        ?int $width,
        ?int $height
    ) {
        $this->type = $type;
        $this->url = $url;
        $this->attributes = $attributes;
        $this->html = $html;
        $this->provider = $provider;
        $this->width = $width;
        $this->height = $height;
    }

    public static function factory(stdClass $value) : self
    {
        $value = $value->value ?? $value;
        $value = $value->oembed ?? $value;

        if (! $value instanceof stdClass || ! isset($value->type, $value->embed_url)) {
            throw new InvalidArgumentException(sprintf(
                'The type and embed_url properties are required elements of the JSON payload. Received: %s',
                json_encode($value)
            ));
        }
        $attributes = array_diff_key(
            (array) $value,
            array_flip(['provider_name', 'type', 'embed_url', 'html', 'height', 'width'])
        );
        return new static(
            $value->type,
            $value->embed_url,
            $attributes,
            $value->html ?? null,
            $value->provider_name ?? null,
            isset($value->width) ? (int) $value->width : null,
            isset($value->height) ? (int) $value->height : null
        );
    }

    public function getProvider() :? string
    {
        return $this->provider;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getHtml() :? string
    {
        return $this->html;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function asText() :? string
    {
        return $this->url;
    }

    public function getWidth() :? int
    {
        return $this->width;
    }

    public function getHeight() :? int
    {
        return $this->height;
    }

    public function attributes() : array
    {
        return $this->attributes;
    }

    /**
     * @deprecated
     */
    public function openTag() : string
    {
        $attributes = [];
        if ($this->provider) {
            $attributes['data-oembed-provider'] = strtolower($this->provider);
        }
        $attributes['data-oembed'] = $this->url;
        $attributes['data-oembed-type'] = $this->type;
        return sprintf(
            '<div%s>',
            $this->htmlAttributes($attributes)
        );
    }

    /**
     * @deprecated
     */
    public function closeTag() : string
    {
        return '</div>';
    }

    public function asHtml(?callable $serializer = null) :? string
    {
        if ($serializer) {
            return $serializer($this);
        }
        return sprintf(
            '%s%s%s',
            $this->openTag(),
            $this->html,
            $this->closeTag()
        );
    }

    public function serialize(Serializer $serializer) :? string
    {
        return $serializer->serialize($this);
    }
}
