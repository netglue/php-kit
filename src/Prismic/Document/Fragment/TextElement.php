<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;
use Prismic\Serializer\HtmlSerializer;
use Prismic\Serializer\Serializer;
use function array_key_exists;
use function json_encode;
use function sprintf;

class TextElement implements FragmentInterface
{

    use HtmlHelperTrait;

    private static $tagMap = [
        'heading1' => 'h1',
        'heading2' => 'h2',
        'heading3' => 'h3',
        'heading4' => 'h4',
        'heading5' => 'h5',
        'heading6' => 'h6',
        'paragraph' => 'p',
        'preformatted' => 'pre',
        'o-list-item' => 'li',
        'list-item' => 'li',
    ];

    private $linkResolver;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $text;

    /**
     * @var array
     */
    private $spans;

    /**
     * @var string|null
     */
    private $label;

    private function __construct(LinkResolver $linkResolver)
    {
        $this->linkResolver = $linkResolver;
    }

    public static function factory($value, LinkResolver $linkResolver) : self
    {
        $element = new static($linkResolver);
        $type = $value->type ?? null;
        if (! $type || ! array_key_exists($type, static::$tagMap)) {
            throw new InvalidArgumentException(sprintf(
                'No Text Element type can be determined from the payload %s',
                json_encode($value)
            ));
        }
        $element->text = $value->text ?? null;
        $element->type = $type;
        $element->spans = $value->spans ?? [];
        $element->label = $value->label ?? null;

        return $element;
    }

    public function asText() : ?string
    {
        return $this->text;
    }

    public function withoutFormatting() :? string
    {
        if (null === $this->text) {
            return null;
        }
        return sprintf(
            '%s%s%s',
            $this->openTag(),
            $this->escapeHtml($this->text),
            $this->closeTag()
        );
    }

    public function asHtml(?callable $serializer = null) :? string
    {
        $serializer = $serializer ?: new HtmlSerializer($this->linkResolver);
        return $serializer($this);
    }

    public function spans() : array
    {
        return $this->spans;
    }

    public function serialize(Serializer $serializer) :? string
    {
        return $serializer->serialize($this);
    }

    public function getTag() : string
    {
        return static::$tagMap[$this->type];
    }

    public function openTag() :? string
    {
        $attributes = $this->label
            ? $this->htmlAttributes(['class' => $this->label])
            : '';
        return sprintf(
            '<%s%s>',
            $this->getTag(),
            $attributes
        );
    }

    public function closeTag() :? string
    {
        return sprintf('</%s>', $this->getTag());
    }
}
