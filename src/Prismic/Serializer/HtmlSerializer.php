<?php
declare(strict_types=1);

namespace Prismic\Serializer;

use Prismic\Document\Fragment\Color;
use Prismic\Document\Fragment\Date;
use Prismic\Document\Fragment\Embed;
use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\FragmentInterface;
use Prismic\Document\Fragment\GeoPoint;
use Prismic\Document\Fragment\Group;
use Prismic\Document\Fragment\HtmlHelperTrait;
use Prismic\Document\Fragment\Image;
use Prismic\Document\Fragment\ImageView;
use Prismic\Document\Fragment\LinkInterface;
use Prismic\Document\Fragment\ListElement;
use Prismic\Document\Fragment\Number;
use Prismic\Document\Fragment\RichText;
use Prismic\Document\Fragment\Slice;
use Prismic\Document\Fragment\Text;
use Prismic\Document\Fragment\TextElement;
use Prismic\LinkResolver;
use function count;
use function implode;
use function sprintf;
use const PHP_EOL;

class HtmlSerializer implements Serializer
{
    use HtmlHelperTrait;

    private $linkResolver;

    public function __construct(LinkResolver $linkResolver)
    {
        $this->linkResolver = $linkResolver;
    }

    public function __invoke(FragmentInterface $fragment) :? string
    {
        return $this->serialize($fragment);
    }

    public function serialize(FragmentInterface $fragment) :? string
    {
        if ($fragment instanceof Color) {
            return $this->color($fragment);
        }
        if ($fragment instanceof Date) {
            return $this->date($fragment);
        }
        if ($fragment instanceof Embed) {
            return $this->embed($fragment);
        }
        if ($fragment instanceof FragmentCollection) {
            return $this->collection($fragment);
        }
        if ($fragment instanceof GeoPoint) {
            return $this->geoPoint($fragment);
        }
        if ($fragment instanceof Group) {
            return $this->group($fragment);
        }
        if ($fragment instanceof Image) {
            return $this->image($fragment);
        }
        if ($fragment instanceof ImageView) {
            return $this->imageView($fragment);
        }
        if ($fragment instanceof LinkInterface) {
            return $this->link($fragment);
        }
        if ($fragment instanceof ListElement) {
            return $this->listElement($fragment);
        }
        if ($fragment instanceof Number) {
            return $this->number($fragment);
        }
        if ($fragment instanceof RichText) {
            return $this->richText($fragment);
        }
        if ($fragment instanceof Slice) {
            return $this->slice($fragment);
        }
        if ($fragment instanceof Text) {
            return $this->text($fragment);
        }
        if ($fragment instanceof TextElement) {
            return $this->textElement($fragment);
        }
        return null;
    }

    private function color(Color $fragment) :? string
    {
        return $fragment->asText();
    }

    private function date(Date $fragment) :? string
    {
        $date = $fragment->asDateTime();
        if ($date) {
            return sprintf(
                '<time datetime="%s">%s</time>',
                $date->format($fragment->format()),
                $fragment->asText()
            );
        }
        return null;
    }

    private function embed(Embed $fragment) :? string
    {
        return sprintf(
            '%s%s%s',
            $fragment->openTag(),
            $fragment->getHtml(),
            $fragment->closeTag()
        );
    }

    private function collection(FragmentCollection $fragment) :? string
    {
        $data = [];
        foreach ($fragment->getFragments() as $child) {
            $data[] = $this->serialize($child);
        }
        if (! count($data)) {
            return null;
        }
        return implode(PHP_EOL, $data);
    }

    private function geoPoint(GeoPoint $geoPoint) :? string
    {
        return sprintf(
            '<span class="geopoint" data-latitude="%1$s" data-longitude="%2$s">%1$s, %2$s</span>',
            $geoPoint->getLatitude(),
            $geoPoint->getLatitude()
        );
    }

    private function group(Group $fragment) :? string
    {
        $data = [];
        foreach ($fragment->getItems() as $child) {
            $data[] = $this->serialize($child);
        }
        if (! count($data)) {
            return null;
        }
        return implode(PHP_EOL, $data);
    }

    private function image(Image $fragment) :? string
    {
        return $this->serialize($fragment->getMain());
    }

    private function imageView(ImageView $fragment) :? string
    {
        $attributes = [
            'src'    => $fragment->getUrl(),
            'width'  => (string) $fragment->getWidth(),
            'height' => (string) $fragment->getHeight(),
            'alt'    => (string) $fragment->getAlt(),
        ];
        if ($fragment->getLabel()) {
            $attributes['class'] = $fragment->getLabel();
        }
        // Use self-closing tag - you never know, someone might still be serving xhtml
        $imageMarkup = sprintf('<img%s />', $this->htmlAttributes($attributes));

        $link = $fragment->getLink();
        if ($link) {
            return sprintf(
                '%s%s%s',
                $link->openTag(),
                $imageMarkup,
                $link->closeTag()
            );
        }

        return $imageMarkup;
    }

    private function listElement(ListElement $fragment) :? string
    {
        if (! $fragment->hasItems()) {
            return null;
        }
        $data = [];
        $data[] = $fragment->openTag();
        foreach ($fragment->getItems() as $item) {
            $data[] = $this->serialize($item);
        }
        $data[] = $fragment->closeTag();
        return implode(PHP_EOL, $data);
    }

    private function link(LinkInterface $fragment) :? string
    {
        $url = $fragment->getUrl();
        if (! $url) {
            return null;
        }
        return sprintf(
            '%s%s%s',
            $fragment->openTag(),
            $this->escapeHtml($url),
            $fragment->closeTag()
        );
    }

    private function number(Number $fragment) :? string
    {
        return $fragment->asText();
    }

    private function richText(RichText $fragment) :? string
    {
        $data = [];
        foreach ($fragment->blocks() as $block) {
            $data[] = $this->serialize($block);
        }
        return implode(PHP_EOL, $data);
    }

    private function slice(Slice $fragment) :? string
    {
        $primary = $this->serialize($fragment->getPrimary());
        $group   = $this->serialize($fragment->getItems());
        if (empty($primary) && empty($group)) {
            return null;
        }

        $attributes = [
            'data-slice-type' => $fragment->getType(),
        ];
        if ($fragment->getLabel()) {
            $attributes['class'] = $fragment->getLabel();
        }
        $data = [
            sprintf('<div%s>', $this->htmlAttributes($attributes)),
        ];
        if ($primary) {
            $data[] = $primary;
        }
        if ($group) {
            $data[] = $group;
        }
        $data[] = '</div>';
        return implode(PHP_EOL, $data);
    }

    private function text(Text $fragment) :? string
    {
        $value = (string) $fragment->asText();
        return ($value === '')
            ? null
            : $this->escapeHtml($value);
    }

    private function textElement(TextElement $fragment) :? string
    {
        if (null === $fragment->asText()) {
            return null;
        }
        return sprintf(
            '%s%s%s',
            $fragment->openTag(),
            $this->insertSpans($fragment->asText(), $fragment->spans(), $this->linkResolver),
            $fragment->closeTag()
        );
    }
}
