<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit;

use Brick\DateTime\LocalDate;
use Brick\DateTime\Period;
use Brick\DateTime\YearWeek;
use Gammadia\DateTimeExtra\Exceptions\IntervalParseException;
use Gammadia\DateTimeExtra\LocalDateInterval;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Gammadia\Collections\Functional\map;

final class LocalDateIntervalTest extends TestCase
{
    public function testConstructThrowsInvalidArgumentExceptionInversedRanges(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Start after end: 2021-01-02 / 2021-01-01');

        LocalDateInterval::between(LocalDate::parse('2021-01-02'), LocalDate::parse('2021-01-01'));
    }

    public function testBetween(): void
    {
        self::assertSame(
            '2010-01-01/2011-01-01',
            (string) LocalDateInterval::between(LocalDate::parse('2010-01-01'), LocalDate::parse('2011-01-01'))
        );
    }

    public function testSince(): void
    {
        self::assertSame('2010-01-01/-', (string) LocalDateInterval::since(LocalDate::parse('2010-01-01')));
    }

    public function testUntil(): void
    {
        self::assertSame('-/2011-01-01', (string) LocalDateInterval::until(LocalDate::parse('2011-01-01')));
    }

    public function testAtomic(): void
    {
        self::assertSame('2009-01-01/2009-01-01', (string) LocalDateInterval::atomic(LocalDate::parse('2009-01-01')));
    }

    /**
     * @dataProvider toLocalDateTimeInterval
     */
    public function testToLocalDateTimeInterval(string $input, string $expected): void
    {
        self::assertSame($expected, (string) LocalDateInterval::parse($input)->toLocalDateTimeInterval());
    }

    /**
     * @return iterable<mixed>
     */
    public function toLocalDateTimeInterval(): iterable
    {
        yield ['-/-', '-/-'];
        yield ['-/2020-10-28', '-/2020-10-29T00:00'];
        yield ['2020-10-28/-', '2020-10-28T00:00/-'];
        yield ['2020-10-28/2020-10-28', '2020-10-28T00:00/2020-10-29T00:00'];
        yield ['2020-10-28/2020-10-29', '2020-10-28T00:00/2020-10-30T00:00'];
        yield ['2020-10-28/2020-11-02', '2020-10-28T00:00/2020-11-03T00:00'];
        yield ['2020-10-28/2024-03-12', '2020-10-28T00:00/2024-03-13T00:00'];
    }

    public function testLengthInDays(): void
    {
        self::assertSame(1, $this->interval('2009|2009')->getLengthInDays());
        self::assertSame(366, $this->interval('2009|2010')->getLengthInDays());
        self::assertSame(367, $this->interval('2012|2013')->getLengthInDays());
        self::assertSame(731, $this->interval('2009|2011')->getLengthInDays());
        self::assertSame(732, $this->interval('2012|2014')->getLengthInDays());

        $this->expectException(\RuntimeException::class);
        $this->interval('----|2009')->getLengthInDays();
    }

    public function testGetPeriod(): void
    {
        self::assertTrue(Period::parse('P30D')->isEqualTo(LocalDateInterval::parse('2014-01-01/2014-01-30')->getPeriod()));
    }

    /**
     * @dataProvider move
     */
    public function testMove(string $iso, string $period, string $expected): void
    {
        self::assertSame($expected, (string) LocalDateInterval::parse($iso)->move(Period::parse($period)));
    }

    /**
     * @return iterable<mixed>
     */
    public function move(): iterable
    {
        yield ['2012-01-01/2012-01-02', 'P1D', '2012-01-02/2012-01-03'];
        yield ['2012-01-01/2012-01-02', 'P1W1D', '2012-01-09/2012-01-10'];
        yield ['2012-01-01/2012-01-02', 'P1M1W1D', '2012-02-09/2012-02-10'];
        yield ['2012-02-09/2012-02-10', '-P1M1W1D', '2012-01-01/2012-01-02'];

        // Leap year
        yield ['2016-02-29/2016-06-01', 'P1Y', '2017-02-28/2017-06-01'];
        yield ['2017-02-28/2017-06-01', '-P1Y', '2016-02-28/2016-06-01'];
        yield ['2016-02-29/2016-06-01', '-P1Y', '2015-02-28/2015-06-01'];
    }

    /**
     * @dataProvider iterateDailyProvider
     */
    public function testIterateDaily(int $expectedCount, string $startIso, string $endIso): void
    {
        self::assertCount(
            $expectedCount,
            iterator_to_array(LocalDateInterval::iterateDaily(LocalDate::parse($startIso), LocalDate::parse($endIso)))
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function iterateDailyProvider(): iterable
    {
        yield [367, '2012-01-01', '2013-01-01'];
        yield [366, '2010-01-01', '2011-01-01'];
        yield [32, '2010-01-01', '2010-02-01'];
        yield [31, '2010-04-01', '2010-05-01'];
        yield [7, '2010-01-01', '2010-01-07'];
        yield [2, '2010-01-01', '2010-01-02'];
        yield [1, '2010-01-01', '2010-01-01'];
    }

    /**
     * @dataProvider iterateProvider
     */
    public function testIterate(int $expected, string $strPeriod): void
    {
        self::assertCount(
            $expected,
            iterator_to_array(LocalDateInterval::parse('2010-01-01/2011-01-01')->iterate(Period::parse($strPeriod)))
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function iterateProvider(): iterable
    {
        yield [366, 'P1D'];
        yield [53, 'P1W'];
        yield [13, 'P1M'];
        yield [2, 'P1Y'];
        yield [183, 'P2D'];
        yield [27, 'P2W'];
        yield [7, 'P2M'];
        yield [1, 'P2Y'];
    }

    public function testParseLocalDateAndPeriod(): void
    {
        self::assertSame('2012-04-01/2012-04-05', (string) LocalDateInterval::parse('2012-04-01/P4D'));
        self::assertSame('2012-04-01/2012-04-05', (string) LocalDateInterval::parse('P4D/2012-04-05'));
    }

    /**
     * @dataProvider providerParseInvalidIntervalsThrowsIntervalParseException
     */
    public function testParseInvalidStringThrowsIntervalParseException(string $text): void
    {
        $this->expectException(IntervalParseException::class);

        LocalDateInterval::parse($text);
    }

    /**
     * @return iterable<mixed>
     */
    public function providerParseInvalidIntervalsThrowsIntervalParseException(): iterable
    {
        yield ['P4D/P2D'];
        yield ['-/P2D'];
        yield ['P4D/-'];
    }

    public function testParseInfinity(): void
    {
        $date = LocalDate::parse('2015-01-01');

        self::assertSame('2015-01-01/-', (string) LocalDateInterval::since($date));
        self::assertSame('-/2015-01-01', (string) LocalDateInterval::until($date));
        self::assertSame('-/-', (string) LocalDateInterval::forever());
    }

    public function testToString(): void
    {
        $iso = '2020-06-29/2020-06-30';
        $interval = LocalDateInterval::parse($iso);

        self::assertSame($iso, (string) $interval);
        self::assertSame($iso, $interval->toString());
    }

    public function testGetStart(): void
    {
        self::assertSame('2020-06-29', (string) LocalDateInterval::parse('2020-06-29/2020-06-30')->getStart());
    }

    public function testGetEnd(): void
    {
        self::assertSame('2020-06-30', (string) LocalDateInterval::parse('2020-06-29/2020-06-30')->getEnd());
    }

    public function testWithStart(): void
    {
        self::assertSame(
            '2020-06-28/2020-06-30',
            (string) LocalDateInterval::parse('2020-06-29/2020-06-30')->withStart(LocalDate::parse('2020-06-28'))
        );
    }

    public function testWithEnd(): void
    {
        self::assertSame(
            '2020-06-29/2020-07-01',
            (string) LocalDateInterval::parse('2020-06-29/2020-06-30')->withEnd(LocalDate::parse('2020-07-01'))
        );
    }

    public function testIsBefore(): void
    {
        self::assertFalse($this->interval('2010|2011')->isBefore(LocalDate::parse('2011-01-01')));
        self::assertFalse($this->interval('2011|2012')->isBefore(LocalDate::parse('2011-01-01')));
        self::assertFalse($this->interval('2012|----')->isBefore(LocalDate::parse('2011-01-01')));
        self::assertTrue($this->interval('----|2010')->isBefore(LocalDate::parse('2011-01-01')));
    }

    public function testIsBeforeInterval(): void
    {
        self::assertFalse($this->interval('2011|2012')->isBeforeInterval($this->interval('2012|2013')));
        self::assertFalse($this->interval('2012|2013')->isBeforeInterval($this->interval('2012|2013')));
        self::assertFalse($this->interval('2013|----')->isBeforeInterval($this->interval('2012|2013')));
        self::assertTrue($this->interval('----|2010')->isBeforeInterval($this->interval('2012|2013')));
        self::assertTrue($this->interval('2010|2011')->isBeforeInterval($this->interval('2012|2013')));
    }

    public function testIsAfter(): void
    {
        self::assertFalse($this->interval('----|2010')->isAfter(LocalDate::parse('2010-01-01')));
        self::assertFalse($this->interval('2010|2011')->isAfter(LocalDate::parse('2010-01-01')));
        self::assertTrue($this->interval('2011|2012')->isAfter(LocalDate::parse('2010-01-01')));
        self::assertTrue($this->interval('2012|----')->isAfter(LocalDate::parse('2010-01-01')));
    }

    public function testIsAfterInterval(): void
    {
        self::assertFalse($this->interval('2011|2012')->isAfterInterval($this->interval('2010|2011')));
        self::assertFalse($this->interval('----|2010')->isAfterInterval($this->interval('2010|2011')));
        self::assertFalse($this->interval('2010|2011')->isAfterInterval($this->interval('2010|2011')));
        self::assertTrue($this->interval('2012|2013')->isAfterInterval($this->interval('2010|2011')));
        self::assertTrue($this->interval('2013|----')->isAfterInterval($this->interval('2010|2011')));
    }

    public function testContains(): void
    {
        self::assertTrue($this->interval('----|2010')->contains(LocalDate::parse('2010-01-01')));
        self::assertTrue($this->interval('2010|2011')->contains(LocalDate::parse('2010-01-01')));
        self::assertTrue($this->interval('2010|----')->contains(LocalDate::parse('2010-01-01')));
    }

    public function testContainsInterval(): void
    {
        self::assertFalse($this->interval('2010|2011')->containsInterval($this->interval('2011|2012')));
        self::assertFalse($this->interval('----|2011')->containsInterval($this->interval('2010|2012')));
        self::assertFalse($this->interval('2012|2013')->containsInterval($this->interval('2011|2012')));
        self::assertFalse($this->interval('----|2012')->containsInterval($this->interval('----|2011')));
        self::assertFalse($this->interval('2011|----')->containsInterval($this->interval('2010|2011')));
        self::assertTrue($this->interval('2010|2013')->containsInterval($this->interval('2011|2012')));
        self::assertTrue($this->interval('2010|2012')->containsInterval($this->interval('2011|2012')));
        self::assertTrue($this->interval('2011|2013')->containsInterval($this->interval('2011|2012')));
        self::assertTrue($this->interval('2011|2012')->containsInterval($this->interval('2011|2012')));
        self::assertTrue($this->interval('----|2012')->containsInterval($this->interval('2010|2011')));
        self::assertTrue($this->interval('----|2011')->containsInterval($this->interval('2010|2011')));
        self::assertTrue($this->interval('2010|----')->containsInterval($this->interval('2010|2011')));
    }

    public function testIsEqualTo(): void
    {
        self::assertTrue($this->interval('----|2011')->isEqualTo($this->interval('----|2011')));
        self::assertTrue($this->interval('2010|2011')->isEqualTo($this->interval('2010|2011')));
        self::assertTrue($this->interval('2012|----')->isEqualTo($this->interval('2012|----')));
        self::assertTrue($this->interval('2012|2013')->isEqualTo($this->interval('2012|2013')));
        self::assertTrue($this->interval('2011|2011')->isEqualTo($this->interval('2011|2011')));
        self::assertTrue($this->interval('2012|----')->isEqualTo($this->interval('2012|----')));

        self::assertFalse($this->interval('----|2011')->isEqualTo($this->interval('2011|2011')));
        self::assertFalse($this->interval('2012|----')->isEqualTo($this->interval('2012|2013')));
        self::assertFalse($this->interval('2010|2011')->isEqualTo($this->interval('2010|----')));
        self::assertFalse($this->interval('2012|2013')->isEqualTo($this->interval('----|2011')));

        self::assertFalse($this->interval('2012|2013')->isEqualTo($this->interval('2011|2013')));
        self::assertFalse($this->interval('----|2013')->isEqualTo($this->interval('----|2012')));
        self::assertFalse($this->interval('2012|----')->isEqualTo($this->interval('2013|----')));
    }

    public function testPrecedes(): void
    {
        self::assertFalse($this->interval('2009|----')->precedes($this->interval('2010|2011')));
        self::assertFalse($this->interval('2009|2010')->precedes($this->interval('----|2011')));
        self::assertFalse($this->interval('2009|2010')->precedes($this->interval('2010|2011')));
        self::assertFalse($this->interval('2009|2010')->precedes($this->interval('2010|2011', '+P1D')));
        self::assertTrue($this->interval('2009|2010')->precedes($this->interval('2010|2011', '+P2D')));
    }

    public function testPrecededBy(): void
    {
        self::assertFalse($this->interval('2010|2011')->precededBy($this->interval('2009|----')));
        self::assertFalse($this->interval('----|2011')->precededBy($this->interval('2009|2010')));
        self::assertFalse($this->interval('2010|2011')->precededBy($this->interval('2009|2010')));
        self::assertFalse($this->interval('2010|2011')->precededBy($this->interval('2009|2010', '-P1D')));
        self::assertTrue($this->interval('2010|2011')->precededBy($this->interval('2009|2010', '-P2D')));
    }

    public function testMeets(): void
    {
        self::assertFalse($this->interval('2009|----')->meets($this->interval('2010|2011')));
        self::assertFalse($this->interval('2009|2010')->meets($this->interval('----|2011')));
        self::assertFalse($this->interval('2009|2010')->meets($this->interval('2010|2011')));
        self::assertTrue($this->interval('2009|2010')->meets($this->interval('2010|2011', '+P1D')));
    }

    public function testMetBy(): void
    {
        self::assertFalse($this->interval('2010|2011')->metBy($this->interval('2009|----')));
        self::assertFalse($this->interval('----|2011')->metBy($this->interval('2009|2010')));
        self::assertFalse($this->interval('2010|2011')->metBy($this->interval('2009|2010')));
        self::assertTrue($this->interval('2010|2011')->metBy($this->interval('2009|2010', '-P1D')));
    }

    public function testOverlaps(): void
    {
        self::assertFalse($this->interval('----|2010')->overlaps($this->interval('----|2013')));
        self::assertFalse($this->interval('2010|2011')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2011|2012')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2011|2014')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2013|----')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2010|----')->overlaps($this->interval('2011|----')));
        self::assertFalse($this->interval('2013|2014')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2013|----')->overlaps($this->interval('2010|2013')));

        self::assertTrue($this->interval('----|2010')->overlaps($this->interval('2010|2013')));
        self::assertTrue($this->interval('2009|2010')->overlaps($this->interval('2010|2013')));
        self::assertTrue($this->interval('----|2010')->overlaps($this->interval('2010|2013', '-P1D')));
        self::assertTrue($this->interval('2009|2010')->overlaps($this->interval('2010|2013', '-P1D')));
    }

    public function testOverlappedBy(): void
    {
        self::assertFalse($this->interval('----|2013')->overlappedBy($this->interval('----|2010')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2010|2011')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2011|2012')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2011|2014')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2013|----')));
        self::assertFalse($this->interval('2011|----')->overlappedBy($this->interval('2010|----')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2013|2014')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2013|----')));

        self::assertTrue($this->interval('2010|2013')->overlappedBy($this->interval('----|2010')));
        self::assertTrue($this->interval('2010|2013')->overlappedBy($this->interval('2009|2010')));
        self::assertTrue($this->interval('2010|2013', '-P1D')->overlappedBy($this->interval('----|2010')));
        self::assertTrue($this->interval('2010|2013', '-P1D')->overlappedBy($this->interval('2009|2010')));
    }

    public function testFinishes(): void
    {
        self::assertFalse($this->interval('2009|2011')->finishes($this->interval('2010|2011')));
        self::assertFalse($this->interval('2010|2011')->finishes($this->interval('2009|2012')));
        self::assertFalse($this->interval('2010|----')->finishes($this->interval('2009|2011')));
        self::assertFalse($this->interval('2010|2011')->finishes($this->interval('2009|----')));
        self::assertFalse($this->interval('----|2011')->finishes($this->interval('2009|2011')));
        self::assertTrue($this->interval('2010|2011')->finishes($this->interval('2009|2011')));
        self::assertTrue($this->interval('2010|----')->finishes($this->interval('2009|----')));
        self::assertTrue($this->interval('2010|2011')->finishes($this->interval('----|2011')));
    }

    public function testFinishedBy(): void
    {
        self::assertFalse($this->interval('2010|2011')->finishedBy($this->interval('2009|2011')));
        self::assertFalse($this->interval('2009|2012')->finishedBy($this->interval('2010|2011')));
        self::assertFalse($this->interval('2009|2011')->finishedBy($this->interval('2010|----')));
        self::assertFalse($this->interval('2009|----')->finishedBy($this->interval('2010|2011')));
        self::assertFalse($this->interval('2009|2011')->finishedBy($this->interval('----|2011')));
        self::assertTrue($this->interval('2009|2011')->finishedBy($this->interval('2010|2011')));
        self::assertTrue($this->interval('2009|----')->finishedBy($this->interval('2010|----')));
        self::assertTrue($this->interval('----|2011')->finishedBy($this->interval('2010|2011')));
    }

    public function testStarts(): void
    {
        self::assertFalse($this->interval('----|2011')->starts($this->interval('----|2011')));
        self::assertFalse($this->interval('----|2011')->starts($this->interval('2010|2011')));
        self::assertFalse($this->interval('2009|----')->starts($this->interval('2009|2013')));
        self::assertFalse($this->interval('2009|----')->starts($this->interval('2009|----')));
        self::assertFalse($this->interval('2009|2012')->starts($this->interval('2009|2011')));
        self::assertFalse($this->interval('2009|2011')->starts($this->interval('2009|2011')));
        self::assertFalse($this->interval('2009|2010')->starts($this->interval('2010|2013')));
        self::assertTrue($this->interval('----|2010')->starts($this->interval('----|2013')));
        self::assertTrue($this->interval('2009|2010')->starts($this->interval('2009|2011')));
        self::assertTrue($this->interval('2009|2010')->starts($this->interval('2009|----')));
    }

    public function testStartedBy(): void
    {
        self::assertFalse($this->interval('----|2011')->startedBy($this->interval('----|2011')));
        self::assertFalse($this->interval('2009|2013')->startedBy($this->interval('2009|----')));
        self::assertFalse($this->interval('2009|----')->startedBy($this->interval('2009|----')));
        self::assertFalse($this->interval('2009|2011')->startedBy($this->interval('2009|2012')));
        self::assertFalse($this->interval('2009|2011')->startedBy($this->interval('2009|2011')));
        self::assertTrue($this->interval('----|2013')->startedBy($this->interval('----|2010')));
        self::assertTrue($this->interval('2009|2011')->startedBy($this->interval('2009|2010')));
        self::assertTrue($this->interval('2009|----')->startedBy($this->interval('2009|2010')));
    }

    public function testEncloses(): void
    {
        self::assertFalse($this->interval('2010|2011')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('2010|2012')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2013')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2012')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('2012|2013')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('----|2012')->encloses($this->interval('----|2011')));
        self::assertFalse($this->interval('----|2011')->encloses($this->interval('2010|2011')));
        self::assertFalse($this->interval('2010|----')->encloses($this->interval('2010|2011')));
        self::assertTrue($this->interval('----|2012')->encloses($this->interval('2010|2011')));
        self::assertTrue($this->interval('2010|2013')->encloses($this->interval('2011|2012')));
        self::assertTrue($this->interval('2010|----')->encloses($this->interval('2010|2011', '+P1D')));
        self::assertTrue($this->interval('2009|----')->encloses($this->interval('2010|2011')));
    }

    public function testEnclosedBy(): void
    {
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2010|2011')));
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2010|2012')));
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2011|2013')));
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2012|2013')));
        self::assertFalse($this->interval('----|2011')->enclosedBy($this->interval('----|2012')));
        self::assertFalse($this->interval('2010|2011')->enclosedBy($this->interval('----|2011')));
        self::assertFalse($this->interval('2010|2011')->enclosedBy($this->interval('2010|----')));
        self::assertTrue($this->interval('2010|2011')->enclosedBy($this->interval('----|2012')));
        self::assertTrue($this->interval('2011|2012')->enclosedBy($this->interval('2010|2013')));
        self::assertTrue($this->interval('2010|2011')->enclosedBy($this->interval('2009|----')));
    }

    public function testIntersects(): void
    {
        self::assertFalse($this->interval('2013|2014')->intersects($this->interval('2010|2012')));
        self::assertTrue($this->interval('2013|2014')->intersects($this->interval('2010|2013')));
        self::assertTrue($this->interval('2013|----')->intersects($this->interval('2010|2013')));
        self::assertTrue($this->interval('----|2010')->intersects($this->interval('2010|2013')));
        self::assertTrue($this->interval('2009|2010')->intersects($this->interval('2010|2013')));
        self::assertTrue($this->interval('2010|2011')->intersects($this->interval('2010|2013')));
        self::assertTrue($this->interval('2011|2012')->intersects($this->interval('2010|2013')));
        self::assertTrue($this->interval('2011|2014')->intersects($this->interval('2010|2013')));
        self::assertTrue($this->interval('----|2010')->intersects($this->interval('2010|2013', '-P1D')));
        self::assertTrue($this->interval('2009|2010')->intersects($this->interval('2010|2013', '-P1D')));
        self::assertTrue($this->interval('2013|2014')->intersects($this->interval('2010|2013', '+P1D')));
        self::assertTrue($this->interval('2013|----')->intersects($this->interval('2010|2013', '+P1D')));
    }

    public function testAbuts(): void
    {
        self::assertFalse($this->interval('2011|2012')->abuts($this->interval('2011|2012')));
        self::assertFalse($this->interval('----|2011')->abuts($this->interval('2011|2012')));
        self::assertFalse($this->interval('2012|2013')->abuts($this->interval('2011|2012')));
        self::assertTrue($this->interval('2010|2011')->abuts($this->interval('2011|2012', '+P1D')));
        self::assertTrue($this->interval('2012|----', '+P1D')->abuts($this->interval('2011|2012')));
        self::assertTrue($this->interval('2012|2013', '+P1D')->abuts($this->interval('2011|2012')));
        self::assertTrue($this->interval('2012|----', '+P1D')->abuts($this->interval('2011|2012')));
    }

    public function testFindIntersection(): void
    {
        self::assertNull($this->interval('2009|2010')->findIntersection($this->interval('2010|2013', '+P1D')));
        self::assertNull($this->interval('2013|2014')->findIntersection($this->interval('2010|2013', '-P1D')));
        self::assertNull($this->interval('2013|----')->findIntersection($this->interval('2010|2013', '-P1D')));
        self::assertNull($this->interval('----|2010')->findIntersection($this->interval('2010|2013', '+P1D')));

        self::assertSame('2010-01-01/2011-01-01', (string) $this->interval('2009|2011')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2010-01-01/2011-01-01', (string) $this->interval('2010|2011')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2011-01-01/2012-01-01', (string) $this->interval('2011|2012')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2011-01-01/2013-01-01', (string) $this->interval('2011|2014')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2010-01-01/2012-01-01', (string) $this->interval('----|2012')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2010-01-01/2012-01-01', (string) $this->interval('2009|2012')->findIntersection($this->interval('2010|----')));
        self::assertSame('-/2012-01-01', (string) $this->interval('----|2012')->findIntersection($this->interval('----|2013')));
        self::assertSame('2010-01-01/-', (string) $this->interval('2009|----')->findIntersection($this->interval('2010|----')));
    }

    /**
     * @dataProvider containerOf
     *
     * @param string[] $input
     */
    public function testContainerOf(array $input, string $expected): void
    {
        self::assertSame(
            $expected,
            (string) LocalDateInterval::containerOf(
                ...map($input, static fn (string $timeRange) =>
                    str_contains($timeRange, 'T')
                        ? LocalDateTimeInterval::parse($timeRange)
                        : LocalDateInterval::parse($timeRange))
            )
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function containerOf(): iterable
    {
        // Empty sets
        yield [['2020-01-01T12:00/2020-01-01T12:00'], '2020-01-01/2020-01-01'];
        yield [['2020-01-01T00:00/2020-01-01T00:00'], '2020-01-01/2020-01-01'];

        // Same same
        yield [['2020-01-01/2020-01-01'], '2020-01-01/2020-01-01'];
        yield [['2020-01-01/2020-01-02'], '2020-01-01/2020-01-02'];

        // Consecutive time ranges
        yield [
            [
                '2020-01-01/2020-01-02',
                '2020-01-02/2020-01-03',
            ],
            '2020-01-01/2020-01-03',
        ];

        // With blanks
        yield [
            [
                '2020-01-01/2020-01-01',
                '2020-01-03/2020-01-04',
                '2020-01-04/2020-01-04',
            ],
            '2020-01-01/2020-01-04',
        ];
    }

    /**
     * @dataProvider forWeek
     *
     * @param array<int, string|null> $others
     */
    public function testForWeek(YearWeek $yearWeek, string $expected): void
    {
        self::assertSame(
            $expected,
            (string) LocalDateInterval::forWeek($yearWeek)
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function forWeek(): iterable
    {
        yield [YearWeek::of(2019, 52), '2019-12-23/2019-12-29'];
        yield [YearWeek::of(2020, 37), '2020-09-07/2020-09-13'];
        yield [YearWeek::of(2021, 04), '2021-01-25/2021-01-31'];
    }

    /**
     * @dataProvider expand
     *
     * @param array<int, string|null> $others
     */
    public function testExpand(string $iso, array $others, string $expected): void
    {
        self::assertSame(
            $expected,
            (string) LocalDateInterval::parse($iso)->expand(
                ...map($others, static fn (?string $timeRange): ?LocalDateInterval
                    => null !== $timeRange ? LocalDateInterval::parse($timeRange) : null
                )
            )
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function expand(): iterable
    {
        $iso = '2020-01-02/2020-01-02';

        // Not actually expanding anything
        yield 'Empty others yield same range' => [$iso, [], $iso];
        yield 'Empty others because of null values yield same range' => [$iso, [null], $iso];
        yield 'Nulls mixed with ranges are skipped' => [$iso, [null, $iso, null], $iso];

        // Expanding
        yield 'Expanding start (finite)' => [$iso, ['2020-01-01/2020-01-02'], '2020-01-01/2020-01-02'];
        yield 'Expanding start (infinite)' => [$iso, ['-/2020-01-02'], '-/2020-01-02'];

        yield 'Expanding end (finite)' => [$iso, ['2020-01-02/2020-01-03'], '2020-01-02/2020-01-03'];
        yield 'Expanding end (infinite)' => [$iso, ['2020-01-02/-'], '2020-01-02/-'];

        yield 'Expanding both (finite)' => [$iso, ['2020-01-01/2020-01-03'], '2020-01-01/2020-01-03'];
        yield 'Expanding both (infinite)' => [$iso, ['-/-'], '-/-'];

        yield 'Expand from multiple ranges' => [
            $iso,
            [
                '2020-01-01/2020-01-02',
                '2020-01-04/2020-01-05',
                '2020-01-06/2020-01-10',
            ],
            '2020-01-01/2020-01-10',
        ];
    }

    /**
     * @dataProvider expandToWeeks
     */
    public function testExpandToWeeks(string $iso, string $expected): void
    {
        self::assertSame($expected, (string) LocalDateInterval::parse($iso)->expandToWeeks());
    }

    /**
     * @return iterable<mixed>
     */
    public function expandToWeeks(): iterable
    {
        yield ['2020-06-10/2020-06-17', '2020-06-08/2020-06-21'];

        // For an exact week, same expected
        yield ['2020-05-04/2020-05-10', '2020-05-04/2020-05-10'];

        // For a whole month
        yield ['2020-09-01/2020-09-30', '2020-08-31/2020-10-04'];

        // For a whole year
        yield ['2020-01-01/2020-12-31', '2019-12-30/2021-01-03'];
    }

    private function interval(string $i, string $strDuration = ''): LocalDateInterval
    {
        $intervals = [
            '----|2009' => LocalDateInterval::until(LocalDate::of(2009, 1, 1)),
            '----|2010' => LocalDateInterval::until(LocalDate::of(2010, 1, 1)),
            '----|2011' => LocalDateInterval::until(LocalDate::of(2011, 1, 1)),
            '----|2012' => LocalDateInterval::until(LocalDate::of(2012, 1, 1)),
            '----|2013' => LocalDateInterval::until(LocalDate::of(2013, 1, 1)),
            '----|2014' => LocalDateInterval::until(LocalDate::of(2014, 1, 1)),
            '2009|2009' => LocalDateInterval::between(LocalDate::of(2009, 1, 1), LocalDate::of(2009, 1, 1)),
            '2009|2010' => LocalDateInterval::between(LocalDate::of(2009, 1, 1), LocalDate::of(2010, 1, 1)),
            '2009|2011' => LocalDateInterval::between(LocalDate::of(2009, 1, 1), LocalDate::of(2011, 1, 1)),
            '2009|2012' => LocalDateInterval::between(LocalDate::of(2009, 1, 1), LocalDate::of(2012, 1, 1)),
            '2009|2013' => LocalDateInterval::between(LocalDate::of(2009, 1, 1), LocalDate::of(2013, 1, 1)),
            '2009|2014' => LocalDateInterval::between(LocalDate::of(2009, 1, 1), LocalDate::of(2014, 1, 1)),
            '2010|2010' => LocalDateInterval::between(LocalDate::of(2010, 1, 1), LocalDate::of(2010, 1, 1)),
            '2010|2011' => LocalDateInterval::between(LocalDate::of(2010, 1, 1), LocalDate::of(2011, 1, 1)),
            '2010|2012' => LocalDateInterval::between(LocalDate::of(2010, 1, 1), LocalDate::of(2012, 1, 1)),
            '2010|2013' => LocalDateInterval::between(LocalDate::of(2010, 1, 1), LocalDate::of(2013, 1, 1)),
            '2010|2014' => LocalDateInterval::between(LocalDate::of(2010, 1, 1), LocalDate::of(2014, 1, 1)),
            '2011|2011' => LocalDateInterval::between(LocalDate::of(2011, 1, 1), LocalDate::of(2011, 1, 1)),
            '2011|2012' => LocalDateInterval::between(LocalDate::of(2011, 1, 1), LocalDate::of(2012, 1, 1)),
            '2011|2013' => LocalDateInterval::between(LocalDate::of(2011, 1, 1), LocalDate::of(2013, 1, 1)),
            '2011|2014' => LocalDateInterval::between(LocalDate::of(2011, 1, 1), LocalDate::of(2014, 1, 1)),
            '2012|2012' => LocalDateInterval::between(LocalDate::of(2012, 1, 1), LocalDate::of(2012, 1, 1)),
            '2012|2013' => LocalDateInterval::between(LocalDate::of(2012, 1, 1), LocalDate::of(2013, 1, 1)),
            '2012|2014' => LocalDateInterval::between(LocalDate::of(2012, 1, 1), LocalDate::of(2014, 1, 1)),
            '2013|2013' => LocalDateInterval::between(LocalDate::of(2013, 1, 1), LocalDate::of(2013, 1, 1)),
            '2013|2014' => LocalDateInterval::between(LocalDate::of(2013, 1, 1), LocalDate::of(2014, 1, 1)),
            '2014|2014' => LocalDateInterval::between(LocalDate::of(2014, 1, 1), LocalDate::of(2014, 1, 1)),
            '2009|----' => LocalDateInterval::since(LocalDate::of(2009, 1, 1)),
            '2010|----' => LocalDateInterval::since(LocalDate::of(2010, 1, 1)),
            '2011|----' => LocalDateInterval::since(LocalDate::of(2011, 1, 1)),
            '2012|----' => LocalDateInterval::since(LocalDate::of(2012, 1, 1)),
            '2013|----' => LocalDateInterval::since(LocalDate::of(2013, 1, 1)),
            '2014|----' => LocalDateInterval::since(LocalDate::of(2014, 1, 1)),
        ];

        return $strDuration ? $intervals[$i]->move(Period::parse($strDuration)) : $intervals[$i];
    }
}
