<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZoneRegion;
use Gammadia\DateTimeExtra\IntervalParseException;
use Gammadia\DateTimeExtra\LocalDateInterval;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use Gammadia\DateTimeExtra\ZonedDateTimeInterval;
use PHPUnit\Framework\TestCase;
use function Gammadia\Collections\Functional\map;

class LocalDateIntervalTest extends TestCase
{
    public function testBetween(): void
    {
        $start = LocalDate::of(2010, 1, 1);
        $end = LocalDate::of(2011, 1, 1);

        self::assertTrue($this->interval('2010|2011')->isEqualTo(LocalDateInterval::between($start, $end)));
    }

    public function testSince(): void
    {
        $start = LocalDate::of(2010, 1, 1);

        self::assertTrue($this->interval('2010|----')->isEqualTo(LocalDateInterval::since($start)));
    }

    public function testUntil(): void
    {
        $end = LocalDate::of(2011, 1, 1);

        self::assertTrue($this->interval('----|2011')->isEqualTo(LocalDateInterval::until($end)));
    }

    public function testAtomic(): void
    {
        $date = LocalDate::of(2009, 1, 1);

        self::assertTrue($this->interval('2009|2009')->isEqualTo(LocalDateInterval::atomic($date)));
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

    public function testAtTimezoneSaoPaulo(): void
    {
        $saoPaulo = TimeZoneRegion::of('America/Sao_Paulo');

        $dateRange = LocalDateInterval::parse('2016-10-16/2016-10-17');
        $ldt1 = LocalDateTime::of(2016, 10, 16);
        $ldt2 = LocalDateTime::of(2016, 10, 18);

        self::assertTrue(
            ZonedDateTimeInterval::between($ldt1->atTimeZone($saoPaulo), $ldt2->atTimeZone($saoPaulo))->isEqualTo(
                $dateRange->atTimeZone($saoPaulo)
            )
        );
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
        $interval =
            LocalDateInterval::between(
                LocalDate::of(2014, 1, 1),
                LocalDate::of(2014, 1, 30)
            );

        self::assertTrue(Period::parse('P30D')->isEqualTo($interval->getPeriod()));
    }

    public function testMove(): void
    {
        self::assertTrue(
            LocalDateInterval::parse('2012-01-02/2012-01-03')->isEqualTo(
                LocalDateInterval::parse('2012-01-01/2012-01-02')->move(Period::parse('P1D'))
            )
        );

        self::assertTrue(
            LocalDateInterval::parse('2012-01-09/2012-01-10')->isEqualTo(
                LocalDateInterval::parse('2012-01-01/2012-01-02')->move(Period::parse('P1W1D'))
            )
        );

        self::assertTrue(
            LocalDateInterval::parse('2012-02-09/2012-02-10')->isEqualTo(
                LocalDateInterval::parse('2012-01-01/2012-01-02')->move(Period::parse('P1M1W1D'))
            )
        );

        self::assertTrue(
            LocalDateInterval::parse('2012-01-01/2012-01-02')->isEqualTo(
                LocalDateInterval::parse('2012-02-09/2012-02-10')->move(Period::parse('-P1M1W1D'))
            )
        );

        //Leap year
        self::assertTrue(
            LocalDateInterval::parse('2017-02-28/2017-06-01')->isEqualTo(
                LocalDateInterval::parse('2016-02-29/2016-06-01')->move(Period::parse('P1Y'))
            )
        );

        self::assertTrue(
            LocalDateInterval::parse('2016-02-28/2016-06-01')->isEqualTo(
                LocalDateInterval::parse('2017-02-28/2017-06-01')->move(Period::parse('-P1Y'))
            )
        );

        self::assertTrue(
            LocalDateInterval::parse('2015-02-28/2015-06-01')->isEqualTo(
                LocalDateInterval::parse('2016-02-29/2016-06-01')->move(Period::parse('-P1Y'))
            )
        );
    }

    /**
     * @dataProvider iterateDailyProvider
     */
    public function testIterateDaily(int $expectedCount, LocalDate $start, LocalDate $end): void
    {
        self::assertCount(
            $expectedCount,
            iterator_to_array(
                LocalDateInterval::iterateDaily($start, $end)
            )
        );
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function iterateDailyProvider(): iterable
    {
        yield [367, LocalDate::of(2012, 1, 1), LocalDate::of(2013, 1, 1)];
        yield [366, LocalDate::of(2010, 1, 1), LocalDate::of(2011, 1, 1)];
        yield [32, LocalDate::of(2010, 1, 1), LocalDate::of(2010, 2, 1)];
        yield [31, LocalDate::of(2010, 4, 1), LocalDate::of(2010, 5, 1)];
        yield [7, LocalDate::of(2010, 1, 1), LocalDate::of(2010, 1, 7)];
        yield [2, LocalDate::of(2010, 1, 1), LocalDate::of(2010, 1, 2)];
    }

    /**
     * @dataProvider iterateProvider
     */
    public function testIterate(int $expectedCount, string $strPeriod): void
    {
        self::assertCount(
            $expectedCount,
            iterator_to_array(
                $this->interval('2010|2011')->iterate(
                    Period::parse($strPeriod)
                )
            )
        );
    }

    /**
     * @return iterable<array<mixed>>
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
        $start = LocalDate::of(2012, 4, 1);
        $end = LocalDate::of(2012, 4, 5);
        $expected = LocalDateInterval::between($start, $end);

        self::assertTrue(LocalDateInterval::parse('2012-04-01/P4D')->isEqualTo($expected));
    }

    public function testParsePeriodAndLocalDate(): void
    {
        $start = LocalDate::of(2012, 4, 1);
        $end = LocalDate::of(2012, 4, 5);
        $expected = LocalDateInterval::between($start, $end);

        self::assertTrue(LocalDateInterval::parse('P4D/2012-04-05')->isEqualTo($expected));
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
     * @return string[][]
     */
    public function providerParseInvalidIntervalsThrowsIntervalParseException(): array
    {
        return [
            ['P4D/P2D'],
            ['-/P2D'],
            ['P4D/-'],
        ];
    }

    public function testParseAlways(): void
    {
        self::assertTrue(LocalDateInterval::parse('-/-')->isEqualTo(LocalDateInterval::between(null, null)));
    }

    public function testParseInfinity(): void
    {
        $tsp = LocalDate::of(2015, 1, 1);

        self::assertTrue(
            LocalDateInterval::parse('2015-01-01/-')->isEqualTo(LocalDateInterval::since($tsp))
        );

        self::assertTrue(
            LocalDateInterval::parse('-/2015-01-01')->isEqualTo(LocalDateInterval::until($tsp))
        );
    }

    public function testToString(): void
    {
        $start = LocalDate::of(2020, 6, 29);
        $end = LocalDate::of(2020, 6, 30);

        self::assertSame('2020-06-29/2020-06-30', LocalDateInterval::between($start, $end)->toString());
        self::assertSame('2009-01-01/-', $this->interval('2009|----')->toString());
        self::assertSame('-/2010-01-01', $this->interval('----|2010')->toString());
    }

    public function testGetStart(): void
    {
        $start = LocalDate::of(2020, 6, 29);
        $end = LocalDate::of(2020, 6, 30);

        self::assertSame($start, LocalDateInterval::between($start, $end)->getStart());
    }

    public function testGetEnd(): void
    {
        $start = LocalDate::of(2020, 6, 29);
        $end = LocalDate::of(2020, 6, 30);

        self::assertSame($end, LocalDateInterval::between($start, $end)->getEnd());
    }

    public function testWithStart(): void
    {
        $start = LocalDate::of(2020, 6, 29);
        $end = LocalDate::of(2020, 6, 30);
        $newStart = LocalDate::of(2020, 6, 28);

        self::assertTrue(LocalDateInterval::between($newStart, $end)->isEqualTo(LocalDateInterval::between($start, $end)->withStart($newStart)));
    }

    public function testWithEnd(): void
    {
        $start = LocalDate::of(2020, 6, 29);
        $end = LocalDate::of(2020, 6, 30);
        $newEnd = LocalDate::of(2020, 7, 1);

        self::assertTrue(LocalDateInterval::between($start, $newEnd)->isEqualTo(LocalDateInterval::between($start, $end)->withEnd($newEnd)));
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

        $intersection = $this->interval('2009|2011')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection && $this->interval('2010|2011')->isEqualTo($intersection));

        $intersection2 = $this->interval('2010|2011')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection2 && $this->interval('2010|2011')->isEqualTo($intersection2));

        $intersection3 = $this->interval('2011|2012')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection3 && $this->interval('2011|2012')->isEqualTo($intersection3));

        $intersection4 = $this->interval('2011|2014')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection4 && $this->interval('2011|2013')->isEqualTo($intersection4));

        $intersection5 = $this->interval('----|2012')->findIntersection($this->interval('----|2013'));
        self::assertTrue($intersection5 && $this->interval('----|2012')->isEqualTo($intersection5));

        $intersection6 = $this->interval('----|2012')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection6 && $this->interval('2010|2012')->isEqualTo($intersection6));

        $intersection7 = $this->interval('2009|----')->findIntersection($this->interval('2010|----'));
        self::assertTrue($intersection7 && $this->interval('2010|----')->isEqualTo($intersection7));

        $intersection5 = $this->interval('2009|2012')->findIntersection($this->interval('2010|----'));
        self::assertTrue($intersection5 && $this->interval('2010|2012')->isEqualTo($intersection5));
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
