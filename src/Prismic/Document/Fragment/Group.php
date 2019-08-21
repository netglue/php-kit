<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;
use Prismic\Serializer\HtmlSerializer;
use Prismic\Serializer\Serializer;
use function count;
use function implode;
use function is_array;
use function json_encode;
use const PHP_EOL;

class Group implements CompositeFragmentInterface
{
    /** @var CompositeFragmentInterface[] */
    private $fragments = [];

    private $linkResolver;

    private function __construct(LinkResolver $linkResolver)
    {
        $this->linkResolver = $linkResolver;
    }

    public static function factory($value, LinkResolver $linkResolver) : self
    {
        $value = $value->value ?? $value;
        /**
         * A Group is a zero indexed array of objects/maps. Each element is a fragment,
         */
        if (! is_array($value)) {
            throw new InvalidArgumentException(\sprintf(
                'Expected an indexed array for group construction, received %s',
                json_encode($value)
            ));
        }
        $group = new static($linkResolver);
        foreach ($value as $collection) {
            /**
             * Groups are used to encapsulate either the elements in group which are a collection
             * Or, as the top-level identifier to encapsulate an array of slices, therefore,
             * the resulting array will contain Multiple or single collections when the type is a group
             * or multiple or single slices
             */
            if (isset($collection->slice_type)) {
                $group->fragments[] = Slice::fromJson($collection, $linkResolver);
            } else {
                $group->fragments[] = FragmentCollection::factory($collection, $linkResolver);
            }
        }
        return $group;
    }

    public static function emptyGroup(LinkResolver $linkResolver) : self
    {
        return new static($linkResolver);
    }

    public function asText() :? string
    {
        $data = [];
        foreach ($this->fragments as $fragment) {
            $data[] = $fragment->asText();
        }
        if (! count($data)) {
            return null;
        }
        return implode(PHP_EOL, $data);
    }

    public function asHtml(?callable $serializer = null) :? string
    {
        $serializer = $serializer ?: new HtmlSerializer($this->linkResolver);
        return $serializer($this);
    }

    /**
     * @return Slice[]|FragmentCollection[]|CompositeFragmentInterface[]
     */
    public function getItems() : array
    {
        return $this->fragments;
    }

    public function serialize(Serializer $serializer) :? string
    {
        return $serializer->serialize($this);
    }
}
