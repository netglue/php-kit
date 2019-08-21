<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Exception\ExceptionInterface;
use Prismic\Ref;
use DateTimeImmutable;
use stdClass;
use function json_decode;
use function strlen;

class RefTest extends TestCase
{

    private $refs;

    /**
     * @return stdClass[]
     */
    public function getRefs() : array
    {
        if (! $this->refs) {
            $this->refs = json_decode($this->getJsonFixture('refs.json'), false);
        }
        $out = [];
        foreach ($this->refs->refs as $ref) {
            $out[] = [$ref];
        }
        return $out;
    }

    /**
     * @dataProvider getRefs
     * @param stdClass $json
     */
    public function testParseRefs(stdClass $json) : void
    {
        $ref = Ref::parse($json);
        $this->assertIsString($ref->getId());
        $this->assertStringMatchesFormat('%s', $ref->getId());
        $this->assertIsString($ref->getRef());
        $this->assertStringMatchesFormat('%s', $ref->getRef());
        $this->assertIsString($ref->getLabel());
        $this->assertStringMatchesFormat('%s', $ref->getLabel());
        $this->assertIsBool($ref->isMasterRef());
        if ($ref->getScheduledAt() !== null) {
            $this->assertIsInt($ref->getScheduledAt());
            $this->assertEquals(13, strlen((string)$ref->getScheduledAt()), 'Expected a 13 digit number');
        }
    }

    /**
     * @dataProvider getRefs
     * @param stdClass $json
     */
    public function testGetScheduledAtTimestamp(stdClass $json) : void
    {
        $ref = Ref::parse($json);

        if ($ref->getScheduledAtTimestamp() !== null) {
            $this->assertIsInt($ref->getScheduledAtTimestamp());
            $this->assertEquals(10, strlen((string)$ref->getScheduledAtTimestamp()), 'Expected a 10 digit number');
        } else {
            // Squash No assertions warning in PHP Unit
            $this->assertNull($ref->getScheduledAtTimestamp());
        }
    }

    /**
     * @dataProvider getRefs
     * @param stdClass $json
     */
    public function testToStringSerialisesToRef(stdClass $json) : void
    {
        $ref = Ref::parse($json);
        $this->assertSame($ref->getRef(), (string) $ref);
    }

    /**
     * @dataProvider getRefs
     * @param stdClass $json
     */
    public function testGetScheduledDate(stdClass $json) : void
    {
        $ref = Ref::parse($json);
        if ($ref->getScheduledAtTimestamp() !== null) {
            $date = $ref->getScheduledDate();
            $this->assertInstanceOf(DateTimeImmutable::class, $date);
            $this->assertSame($ref->getScheduledAtTimestamp(), $date->getTimestamp());
            $this->assertNotSame($date, $ref->getScheduledDate(), 'Returned date should be a new instance every time');
            $this->assertSame('UTC', $date->getTimezone()->getName());
        } else {
            $this->assertNull($ref->getScheduledDate());
        }
    }

    public function testExceptionThrownForInvalidJsonObject() : void
    {
        $this->expectException(ExceptionInterface::class);
        Ref::parse(new stdClass);
    }
}
