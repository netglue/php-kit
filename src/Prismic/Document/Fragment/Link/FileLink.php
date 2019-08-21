<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\HtmlHelperTrait;
use Prismic\Document\Fragment\LinkInterface;
use Prismic\LinkResolver;

class FileLink extends WebLink
{

    use HtmlHelperTrait;

    /**
     * @var string|null
     */
    protected $filename;

    /**
     * @var int|null
     */
    protected $filesize;

    public static function linkFactory($value, LinkResolver $linkResolver) : LinkInterface
    {
        /** @var FileLink $link */
        $link = parent::linkFactory($value, $linkResolver);
        // V1
        $value = $value->value ?? $value;
        $value = $value->file ?? $value;

        $link->filename = $value->name ?? null;
        $link->filesize = isset($value->size) ? (int) $value->size : null;

        return $link;
    }

    public function getFilesize() :? int
    {
        return $this->filesize;
    }

    public function getFilename() :? string
    {
        return $this->filename;
    }

    public function asHtml(?callable $serializer = null) :? string
    {
        if ($serializer) {
            return $serializer($this);
        }
        $label = $this->filename ?: $this->url;
        return sprintf(
            '%s%s%s',
            $this->openTag(),
            $this->escapeHtml($label),
            $this->closeTag()
        );
    }
}
