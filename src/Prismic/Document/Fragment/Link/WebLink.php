<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\LinkInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;
use function json_encode;
use function sprintf;

class WebLink extends AbstractLink
{

    /** @var string */
    protected $url;


    public function getUrl() : ?string
    {
        return $this->url;
    }

    public static function linkFactory($value, LinkResolver $linkResolver) : LinkInterface
    {
        /** @var WebLink $link */
        $link = new static($linkResolver);
        $value = $value->value ?? $value;
        $value = $value->image ?? $value;
        $value = $value->file ?? $value;

        if (! isset($value->url)) {
            throw new InvalidArgumentException(sprintf(
                'Expected value to contain a url property, received %s',
                json_encode($value)
            ));
        }

        $link->url    = $value->url;
        $link->target = $value->target ?? null;
        return $link;
    }
}
