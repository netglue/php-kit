<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Predicates;

class PredicatesTest extends TestCase
{

    public function atProvider() : array
    {
        return [
            ['document.type', 'blog-post', '[:d = at(document.type, "blog-post")]'],
            ['my.doc-type.frag-name', 'foo', '[:d = at(my.doc-type.frag-name, "foo")]'],
            ['document.tags', ['one', 'two', 'three'], '[:d = at(document.tags, ["one", "two", "three"])]'],
        ];
    }

    /**
     * @dataProvider atProvider
     */
    public function testAtPredicate(string $fragment, $value, string $expect)
    {
        $predicate = Predicates::at($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    public function notProvider() : array
    {
        return [
            ['document.type', 'blog-post', '[:d = not(document.type, "blog-post")]'],
            ['my.doc-type.frag-name', 'foo', '[:d = not(my.doc-type.frag-name, "foo")]'],
            ['my.doc-type.price', 100, '[:d = not(my.doc-type.price, 100)]'],
            ['document.tags', ['one', 'two', 'three'], '[:d = not(document.tags, ["one", "two", "three"])]'],
        ];
    }

    /**
     * @dataProvider notProvider
     */
    public function testNotPredicate(string $fragment, $value, string $expect)
    {
        $predicate = Predicates::not($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    public function anyProvider() : array
    {
        return [
            ['document.id', ['id1', 'id2'], '[:d = any(document.id, ["id1", "id2"])]'],
            ['document.tags', ['one', 'two', 'three'], '[:d = any(document.tags, ["one", "two", "three"])]'],
            ['document.tags', ['one'], '[:d = any(document.tags, ["one"])]'],
        ];
    }

    /**
     * @dataProvider anyProvider
     */
    public function testAnyPredicate(string $fragment, $value, string $expect)
    {
        $predicate = Predicates::any($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    public function inProvider() : array
    {
        return [
            ['document.id', ['id1', 'id2'], '[:d = in(document.id, ["id1", "id2"])]'],
            ['my.page.uid', ['uid1', 'uid2'], '[:d = in(my.page.uid, ["uid1", "uid2"])]'],
        ];
    }

    /**
     * @dataProvider inProvider
     */
    public function testInPredicate(string $fragment, $value, string $expect)
    {
        $predicate = Predicates::in($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    public function testHasPredicate()
    {
        $predicate = Predicates::has("my.article.author");
        $this->assertEquals('[:d = has(my.article.author)]', $predicate->q());
    }

    public function testMissingPredicate()
    {
        $predicate = Predicates::missing("my.article.author");
        $this->assertEquals('[:d = missing(my.article.author)]', $predicate->q());
    }

    public function testFulltextPredicate()
    {
        $predicate = Predicates::fulltext("document", "some value");
        $this->assertEquals('[:d = fulltext(document, "some value")]', $predicate->q());
    }

    public function testSimilarPredicate()
    {
        $predicate = Predicates::similar("someId", 5);
        $this->assertEquals('[:d = similar("someId", 5)]', $predicate->q());
    }

    public function ltProvider() : array
    {
        return [
            ['my.page.num', 1, '[:d = number.lt(my.page.num, 1)]'],
            ['my.page.num', 1.1, '[:d = number.lt(my.page.num, 1.1)]'],
            ['my.page.num', "2", '[:d = number.lt(my.page.num, "2")]'],
        ];
    }

    /**
     * @dataProvider ltProvider
     */
    public function testNumberLT(string $fragment, $value, string $expect)
    {
        $predicate = Predicates::lt($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     */
    public function testLtThrowsExceptionForNonNumber()
    {
        Predicates::lt("my.product.price", 'foo');
    }

    public function gtProvider() : array
    {
        return [
            ['my.page.num', 1, '[:d = number.gt(my.page.num, 1)]'],
            ['my.page.num', 1.1, '[:d = number.gt(my.page.num, 1.1)]'],
            ['my.page.num', "2", '[:d = number.gt(my.page.num, "2")]'],
        ];
    }

    /**
     * @dataProvider gtProvider
     */
    public function testNumberGt(string $fragment, $value, string $expect)
    {
        $predicate = Predicates::gt($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     */
    public function testGtThrowsExceptionForNonNumber()
    {
        Predicates::gt("my.product.price", 'foo');
    }

    public function rangeProvider() : array
    {
        return [
            ['my.page.num', 1, 2,  '[:d = number.inRange(my.page.num, 1, 2)]'],
            ['my.page.num', 1.1, 2.2,  '[:d = number.inRange(my.page.num, 1.1, 2.2)]'],
            ['my.page.num', "2", "3", '[:d = number.inRange(my.page.num, "2", "3")]'],
        ];
    }

    /**
     * @dataProvider rangeProvider
     */
    public function testNumberInRange(string $fragment, $low, $high, string $expect)
    {
        $predicate = Predicates::inRange($fragment, $low, $high);
        $this->assertEquals($expect, $predicate->q());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     */
    public function testExceptionThrownByInRangeForNonNumbers()
    {
        Predicates::inRange('my.whatever', 'foo', 'foo');
    }

    public function testDateBefore()
    {
        $predicate = Predicates::dateBefore('foo', 1);
        $this->assertEquals('[:d = date.before(foo, 1)]', $predicate->q());
        $predicate = Predicates::dateBefore('foo', '2018-01-01');
        $this->assertEquals('[:d = date.before(foo, "2018-01-01")]', $predicate->q());

        $date = \DateTime::createFromFormat('!U', '1');
        $predicate = Predicates::dateBefore('foo', $date);
        $this->assertEquals('[:d = date.before(foo, 1000)]', $predicate->q());
    }

    public function testDateAfter()
    {
        $predicate = Predicates::dateAfter('foo', 1);
        $this->assertEquals('[:d = date.after(foo, 1)]', $predicate->q());
        $predicate = Predicates::dateAfter('foo', '2018-01-01');
        $this->assertEquals('[:d = date.after(foo, "2018-01-01")]', $predicate->q());

        $date = \DateTime::createFromFormat('!U', '1');
        $predicate = Predicates::dateAfter('foo', $date);
        $this->assertEquals('[:d = date.after(foo, 1000)]', $predicate->q());
    }

    public function testDateBetween()
    {
        $predicate = Predicates::dateBetween('foo', 1, 2);
        $this->assertEquals('[:d = date.between(foo, 1, 2)]', $predicate->q());
        $predicate = Predicates::dateBetween('foo', '2018-01-01', '2018-01-02');
        $this->assertEquals('[:d = date.between(foo, "2018-01-01", "2018-01-02")]', $predicate->q());

        $date = \DateTime::createFromFormat('!U', '1');
        $predicate = Predicates::dateBetween('foo', $date, $date);
        $this->assertEquals('[:d = date.between(foo, 1000, 1000)]', $predicate->q());
    }

    public function testDayOfMonth()
    {
        $predicate = Predicates::dayOfMonth('foo', 1);
        $this->assertEquals('[:d = date.day-of-month(foo, 1)]', $predicate->q());
        $predicate = Predicates::dayOfMonth('foo', '5');
        $this->assertEquals('[:d = date.day-of-month(foo, "5")]', $predicate->q());

        $date = \DateTime::createFromFormat('!U', '1');
        $predicate = Predicates::dayOfMonth('foo', $date);
        $this->assertEquals('[:d = date.day-of-month(foo, 1)]', $predicate->q());

        $predicate = Predicates::dayOfMonthBefore('foo', $date);
        $this->assertEquals('[:d = date.day-of-month-before(foo, 1)]', $predicate->q());

        $predicate = Predicates::dayOfMonthAfter('foo', $date);
        $this->assertEquals('[:d = date.day-of-month-after(foo, 1)]', $predicate->q());
    }

    public function testDayOfWeek()
    {
        $date = \DateTime::createFromFormat('!U', '1');

        $predicate = Predicates::dayOfWeek('foo', $date);
        $this->assertEquals('[:d = date.day-of-week(foo, 4)]', $predicate->q());

        $predicate = Predicates::dayOfWeekAfter('foo', $date);
        $this->assertEquals('[:d = date.day-of-week-after(foo, 4)]', $predicate->q());

        $predicate = Predicates::dayOfWeekBefore('foo', $date);
        $this->assertEquals('[:d = date.day-of-week-before(foo, 4)]', $predicate->q());
    }

    public function testMonth()
    {
        $date = \DateTime::createFromFormat('!U', '1');

        $predicate = Predicates::month("foo", $date);
        $this->assertEquals('[:d = date.month(foo, 1)]', $predicate->q());

        $predicate = Predicates::monthAfter("foo", $date);
        $this->assertEquals('[:d = date.month-after(foo, 1)]', $predicate->q());

        $predicate = Predicates::monthBefore("foo", $date);
        $this->assertEquals('[:d = date.month-before(foo, 1)]', $predicate->q());
    }

    public function testYear()
    {
        $date = \DateTime::createFromFormat('!U', '1');

        $predicate = Predicates::year("foo", $date);
        $this->assertEquals('[:d = date.year(foo, 1970)]', $predicate->q());
    }

    public function testHour()
    {
        $date = \DateTime::createFromFormat('!U', '1');

        $predicate = Predicates::hour("foo", $date);
        $this->assertEquals('[:d = date.hour(foo, 0)]', $predicate->q());

        $predicate = Predicates::hourAfter("foo", $date);
        $this->assertEquals('[:d = date.hour-after(foo, 0)]', $predicate->q());

        $predicate = Predicates::hourBefore("foo", $date);
        $this->assertEquals('[:d = date.hour-before(foo, 0)]', $predicate->q());
    }

    public function testGeopointNear()
    {
        $p = Predicates::near("my.store.coordinates", 40.689757, -74.0451453, 15);
        $this->assertEquals("[:d = geopoint.near(my.store.coordinates, 40.689757, -74.0451453, 15)]", $p->q());
    }
}
