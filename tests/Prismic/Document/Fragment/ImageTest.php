<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\Image;
use Prismic\Document\Fragment\Link\WebLink;
use Prismic\Document\Fragment\RichText;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class ImageTest extends TestCase
{

    private $collection;

    public function setUp()
    {
        parent::setUp();
        $data = \json_decode($this->getJsonFixture('fragments/image.json'));
        $this->collection = FragmentCollection::factory($data, new FakeLinkResolver());
    }

    public function testFixtureImagesAllReturnImages()
    {
        foreach (['single-image-v2', 'multi-image-v2', 'single-image-v1', 'multi-image-v1'] as $key) {
            /** @var Image $image */
            $image = $this->collection->get($key);
            $this->assertInstanceOf(Image::class, $image);
        }
        foreach (['richtext-v1', 'richtext-v2'] as $key) {
            /** @var RichText $richtext */
            $richtext = $this->collection->get($key);
            $images = $richtext->getImages();
            $this->assertContainsOnlyInstancesOf(Image::class, $images);
        }
    }

    public function invalidImagePayloadProvider()
    {
        return [
            ['{}'],
            ['{"url": "An URL"}'],
            ['{"url": "An URL", "dimensions": {}}'],
            ['{"url": "An URL", "dimensions": {"width": null}}'],
            ['{"url": "An URL", "dimensions": {"width": 10}}'],
            ['{"url": "An URL", "dimensions": {"width": 10, "height": null}}'],
            ['{"url": "An URL", "dimensions": {"width": 10, "height": "foo"}}'],
        ];
    }

    /**
     * @dataProvider invalidImagePayloadProvider
     * @expectedException \Prismic\Exception\InvalidArgumentException
     */
    public function testInvalidPayloadCases(string $jsonString)
    {
        Image::factory(\json_decode($jsonString), new FakeLinkResolver());
    }

    public function testImageFragmentBasics()
    {
        /** @var Image $image */
        $image = $this->collection->get('single-image-v2');
        $this->assertInstanceOf(Image::class, $image);
        $this->assertInternalType('string', $image->getAlt());
        $this->assertInternalType('integer', $image->getWidth());
        $this->assertInternalType('integer', $image->getHeight());
        $this->assertInternalType('string', $image->getUrl());
        $this->assertNull($image->getLink());
        $this->assertFalse($image->hasLink());
        $this->assertNull($image->getLabel());
        $this->assertInternalType('string', $image->getCopyright());
        $views = $image->getViews();
        $this->assertInternalType('array', $views);
        $this->assertCount(1, $views);
        $this->assertArrayHasKey('main', $views);
        $this->assertInternalType('float', $image->ratio());
        $this->assertSame($image->getUrl(), $image->asText());
    }

    public function testHtmlIsCorrectlyRenderedWhenThereIsNoLink()
    {
        /** @var Image $image */
        $image = $this->collection->get('single-image-v2');
        $html = $image->asHtml();
        $this->assertSame('<img src="IMAGE&#x20;URL" width="960" height="800" alt="ALT&#x20;TEXT" />', $html);
    }

    public function testLabelIsAddedAsCssClassWhenPresent()
    {
        $json = '{
            "url" : "URL",
            "dimensions" : {
                "width" : 10,
                "height" : 10
            },
            "label" : "LABEL"
        }';
        $image = Image::factory(\json_decode($json), new FakeLinkResolver());
        $this->assertSame('LABEL', $image->getLabel());
        $this->assertSame('<img src="URL" width="10" height="10" alt="" class="LABEL" />', $image->asHtml());
    }

    public function testLinkIsRetrievableWhenPresentInJsonPayload()
    {
        $json = '{
            "type": "image",
            "url": "URL",
            "dimensions": {
                "width": 10,
                "height": 10
            },
            "linkTo": {
                "link_type": "Web",
                "url": "URL"
            }
        }';
        /** @var Image $image */
        $image = Image::factory(\json_decode($json), new FakeLinkResolver());
        $this->assertTrue($image->hasLink());
        $this->assertInstanceOf(WebLink::class, $image->getLink());
        $expect = '<a href="URL"><img src="URL" width="10" height="10" alt="" /></a>';
        $this->assertSame($expect, $image->asHtml());
    }
}
