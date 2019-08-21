<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;
use Prismic\Serializer\HtmlSerializer;
use Prismic\Serializer\Serializer;
use function count;
use function implode;
use const PHP_EOL;

class Slice implements CompositeFragmentInterface
{

    use HtmlHelperTrait;

    /**
     * @var FragmentCollection
     */
    private $primary;

    /**
     * @var Group
     */
    private $group;

    /** @var string */
    private $type;

    /** @var string|null */
    private $label;

    private $linkResolver;

    private function __construct(
        string $type,
        FragmentCollection $primary,
        Group $group,
        LinkResolver $linkResolver,
        ?string $label = null
    ) {
        $this->type    = $type;
        $this->label   = $label;
        $this->primary = $primary;
        $this->group   = $group;
        $this->linkResolver = $linkResolver;
    }

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        return static::fromJson($value, $linkResolver);
    }

    public static function fromJson($value, LinkResolver $linkResolver) : self
    {
        // Type and Label are the same for V1 & V2
        $type    = isset($value->slice_type)
                 ? (string) $value->slice_type
                 : null;
        $label   = isset($value->slice_label)
                 ? (string) $value->slice_label
                 : null;

        if (! $type) {
            throw new InvalidArgumentException('No Slice type could be determined from the payload');
        }

        // V1
        $group   = isset($value->repeat)
                 ? Group::factory($value->repeat, $linkResolver)
                 : null;
        $primary = isset($value->{'non-repeat'})
                 ? FragmentCollection::factory($value->{'non-repeat'}, $linkResolver)
                 : null;
        /**
         * In much older versions of the API (Before "Composite Slices"), slices
         * had a 'value' property which contained the repeatable group
         */
        if (! $group && isset($value->value) && isset($value->value->type) && $value->value->type === 'Group') {
            $group = Group::factory($value->value, $linkResolver);
        }

        // V2
        $group   = isset($value->items)
                 ? Group::factory($value->items, $linkResolver)
                 : $group;
        $primary = isset($value->primary)
                 ? FragmentCollection::factory($value->primary, $linkResolver)
                 : $primary;

        $group = $group ?: Group::emptyGroup($linkResolver);
        $primary = $primary ?: FragmentCollection::emptyCollection($linkResolver);

        return new static($type, $primary, $group, $linkResolver, $label);
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getLabel() :? string
    {
        return $this->label;
    }

    public function getPrimary() : FragmentCollection
    {
        return $this->primary;
    }

    public function getItems() : Group
    {
        return $this->group;
    }

    public function asText() :? string
    {
        $data = [];
        $primary = $this->primary->asText();
        if ($primary) {
            $data[] = $primary;
        }
        $group = $this->group->asText();
        if ($group) {
            $data[] = $group;
        }
        return count($data) >= 1
            ? implode(PHP_EOL, $data)
            : null;
    }

    public function asHtml(?callable $serializer = null) :? string
    {
        $serializer = $serializer ?: new HtmlSerializer($this->linkResolver);
        return $serializer($this);
    }

    public function serialize(Serializer $serializer): ?string
    {
        return $serializer->serialize($this);
    }
}
