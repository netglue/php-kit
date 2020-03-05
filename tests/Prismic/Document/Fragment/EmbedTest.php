<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Embed;
use Prismic\Document\Fragment\FragmentInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Serializer\Serializer;
use Prismic\Test\TestCase;
use stdClass;
use function json_decode;

class EmbedTest extends TestCase
{

    /**
     * @var Embed
     */
    private $embed;

    protected function setUp() : void
    {
        parent::setUp();
        $data = json_decode($this->getJsonFixture('fragments/embed.json'), false);
        $this->embed = Embed::factory($data);
    }

    public function testExceptionThrownWithNoEmbedUrl() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type and embed_url properties are required elements of the JSON payload');
        Embed::factory(new stdClass());
    }

    public function testExpectedValues() : void
    {
        $this->assertSame('YouTube', $this->embed->getProvider());
        $this->assertSame('video', $this->embed->getType());
        $this->assertSame('EMBED_URL', $this->embed->getUrl());
        $this->assertSame('EMBED_URL', $this->embed->asText());
        $this->assertSame('EMBED_HTML_STRING', $this->embed->getHtml());
        $this->assertSame(500, $this->embed->getWidth());
        $this->assertSame(500, $this->embed->getHeight());
        $this->assertSame('<div data-oembed-provider="youtube" data-oembed="EMBED_URL" data-oembed-type="video">EMBED_HTML_STRING</div>', $this->embed->asHtml());
    }

    public function testOtherOembedPropertiesAreAvailableInAttributesMethod() : void
    {
        $attributes = $this->embed->attributes();
        $this->assertSame('Author', $attributes['author_name']);
        $this->assertSame('1.0', $attributes['version']);
        $this->assertSame('THUMB_URL', $attributes['thumbnail_url']);
    }

    public function testCallableFunctionCanBeUsedToSerialiseToHtml() : void
    {
        $html = $this->embed->asHtml(static function (Embed $embed) : string {
            return $embed->getProvider();
        });
        $this->assertSame('YouTube', $html);
    }

    public function testCustomSerializer() : void
    {
        $serializer = new class implements Serializer {
            public function __invoke(FragmentInterface $fragment) : string
            {
                return $this->serialize($fragment);
            }
            public function serialize(FragmentInterface $fragment) : string
            {
                return $fragment instanceof Embed ? $fragment->getProvider() : '';
            }
        };
        $this->assertSame('YouTube', $this->embed->serialize($serializer));
    }
}
