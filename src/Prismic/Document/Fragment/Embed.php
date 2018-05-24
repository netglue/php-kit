<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;

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
    private $attributes = [];

    private function __construct()
    {
    }

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        $value = isset($value->value) ? $value->value : $value;
        $value = isset($value->oembed) ? $value->oembed : $value;

        if (! isset($value->type) || ! isset($value->embed_url)) {
            throw new InvalidArgumentException(\sprintf(
                'The type and embed_url properties are required elements of the JSON payload. Received: %s',
                \json_encode($value)
            ));
        }

        $embed = new static;
        $embed->provider = isset($value->provider_name) ? $value->provider_name : null;
        $embed->type = $value->type;
        $embed->url  = $value->embed_url;
        $embed->html = isset($value->html) ? $value->html : null;
        $embed->height = isset($value->height) ? (int) $value->height : null;
        $embed->width = isset($value->width) ? (int) $value->width : null;

        $embed->attributes = \array_diff_key(
            (array) $value,
            \array_flip(['provider_name', 'type', 'embed_url', 'html', 'height', 'width'])
        );
        return $embed;
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

    public function openTag() : string
    {
        $attributes = [];
        if ($this->provider) {
            $attributes['data-oembed-provider'] = \strtolower($this->provider);
        }
        $attributes['data-oembed'] = $this->url;
        $attributes['data-oembed-type'] = $this->type;
        return \sprintf(
            '<div%s>',
            $this->htmlAttributes($attributes)
        );
    }

    public function closeTag() : string
    {
        return '</div>';
    }

    public function asHtml() :? string
    {
        return \sprintf(
            '%s%s%s',
            $this->openTag(),
            $this->html,
            $this->closeTag()
        );
    }
}
