<?php

namespace Gammadia\DateTimeExtra\Test\Unit\LocalDateTime;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;
use Gammadia\DateTimeExtra\Instant\InstantInterval;
use Gammadia\DateTimeExtra\LocalDateTime\Converter;
use Gammadia\DateTimeExtra\LocalDateTime\LocalDateTimeInterval;
use Gammadia\DateTimeExtra\Share\IntervalParseException;
use Gammadia\DateTimeExtra\ZonedDateTime\ZonedDateTimeInterval;
use PHPUnit\Framework\TestCase;

class LocalDateTimeIntervalTest extends TestCase
{
    public function testToIsoString(): void
    {
        $start = LocalDateTime::parse('2007-01-01T10:15:30');
        $end = LocalDateTime::parse('2007-02-14T10:15:30');
        $interval = LocalDateTimeInterval::between($start, $end);

        self::assertEquals('2007-01-01T10:15:30/2007-02-14T10:15:30', $interval->toIsoString());
    }

    public function testToIsoStringInfinity(): void
    {
        $since = LocalDateTimeInterval::since(LocalDateTime::of(2016, 2, 28, 13, 20));
        $until = LocalDateTimeInterval::until(LocalDateTime::of(2016, 2, 28, 13, 20));

        self::assertEquals('2016-02-28T13:20/-', $since->toIsoString());
        self::assertEquals('-/2016-02-28T13:20', $until->toIsoString());
    }

    public function testInUTC(): void
    {
        $t1 = LocalDateTime::of(2014, 2, 27, 0, 0);
        $t2 = LocalDateTime::of(2014, 5, 14, 23, 59, 59);

        $m1 = Converter::toInstant($t1, TimeZoneOffset::utc());
        $m2 = Converter::toInstant($t2, TimeZoneOffset::utc());

        self::assertEquals(LocalDateTimeInterval::between($t1, $t2)->atUTC(), InstantInterval::between($m1, $m2));
    }

    public function testInTimezoneSaoPaulo(): void
    {
        $saoPaulo = TimeZoneRegion::of('America/Sao_Paulo');

        $ldt1 = LocalDateTime::of(2016, 10, 16, 1, 0);
        $ldt2 = LocalDateTime::of(2016, 10, 16, 2, 0);

        self::assertEquals(
            LocalDateTimeInterval::between($ldt1, $ldt2)->atTimeZone($saoPaulo),
            ZonedDateTimeInterval::between($ldt1->atTimeZone($saoPaulo), $ldt2->atTimeZone($saoPaulo))
        );
    }

    public function testParseLocalDateTimeAndPeriod(): void
    {
        $start = LocalDateTime::of(2012, 4, 1, 14, 15);
        $end = LocalDateTime::of(2012, 4, 5, 16, 0);
        $expected = LocalDateTimeInterval::between($start, $end);

        self::assertEquals(
            LocalDateTimeInterval::parse("2012-04-01T14:15/P4DT1H45M"),
            $expected
        );
    }

    public function testParsePeriodAndLocalDateTime(): void
    {
        $start = LocalDateTime::of(2012, 4, 1, 14, 15);
        $end = LocalDateTime::of(2012, 4, 5, 16, 0);
        $expected = LocalDateTimeInterval::between($start, $end);

        self::assertEquals(
            $expected,
            LocalDateTimeInterval::parse("P4DT1H45M/2012-04-05T16:00")
        );
    }

    /**
     * @dataProvider providerParseInvalidIntervalsThrowsIntervalParseException
     *
     * @param string $text
     */
    public function testParseInvalidStringThrowsIntervalParseException(string $text): void
    {
        $this->expectException(IntervalParseException::class);

        LocalDateTimeInterval::parse($text);
    }

    public function providerParseInvalidIntervalsThrowsIntervalParseException(): array
    {
        return [
            ["P4DT1H45M/P2DT1H45M"],
            ["-/P2DT1H45M"],
            ["P4DT1H45M/-"],
        ];
    }

    /**
     * @dataProvider providerParseInvalidIntervalsThrowsException
     *
     * @param string $text
     */
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);

        LocalDateTimeInterval::parse($text);
    }

    /**
     * @return array<array<string>>
     */
    public function providerParseInvalidIntervalsThrowsException(): array
    {
        return [
            ["2012-04-30T14:15/T16:00"],
            ["2012-04-30T14:15/T24:00"],
            ["2012-04-30T14:15/16:00"],
            ["2012-092T14:15/096T16:00"],
            ["2012-W13-7T14:15/W14-4T16:00"],
            ["2012092T1415/096T1600"],
            ["2012W137T1415/W144T1600"],
            ["2012-092T14:15/2012-096T16:00"],
            ["2012-W13-7T14:15/2012-W14-4T16:00"],
            ["2012092T1415/2012096T1600"],
            ["2012W137T1415/2012W144T1600"],
            ["2012-04-01T14:15/P2M4DT1H45M"],
            ["2012-092T14:15/P4DT1H45M"],
            ["2012-W13-7T14:15/P0000-00-04T01:45"],
            ["P4DT1H45M/2012-096T16:00"],
            ["P0000-00-04T01:45/2012-W14-4T16:00"],
            ["2015-01-01T08:45/+∞"],
            ["-∞/2015-01-01T08:45"],
            ["2015-01-01T08:45/+999999999-12-31T23:59:59,999999999"],
            ["-∞/+∞"],
        ];
    }

    public function testParseAlways(): void
    {
        $always = LocalDateTimeInterval::between(null, null);
        self::assertEquals(LocalDateTimeInterval::parse("-/-"), $always);
    }

    public function testParseInfinity(): void
    {
        $tsp = LocalDateTime::of(2015, 1, 1, 8, 45);

        self::assertEquals(
            LocalDateTimeInterval::parse("2015-01-01T08:45/-"),
            LocalDateTimeInterval::since($tsp)
        );
        self::assertEquals(
            LocalDateTimeInterval::parse("-/2015-01-01T08:45"),
            LocalDateTimeInterval::until($tsp)
        );
    }

    public function testGetDurationOfLocalDateTimeIntervalWithZonalCorrection(): void
    {
        $interval =
            LocalDatetimeInterval::between(
                LocalDatetime::of(2014, 1, 1, 21, 45),
                LocalDatetime::of(2014, 1, 31, 7, 0)
            );

        self::assertEquals(
            Duration::parse('P29DT9H15M'),
            $interval->getDuration()
        );
    }

    public function testMoveWithDuration(): void
    {
        //Duration
        self::assertEquals(
            LocalDateTimeInterval::parse('2012-04-10T00:00:00/2012-12-31T23:59:59')->move(Duration::parse('PT1S')),
            LocalDateTimeInterval::parse('2012-04-10T00:00:01/2013-01-01T00:00:00')
        );
        self::assertEquals(
            LocalDateTimeInterval::parse('2012-04-10T00:00:01/2013-01-01T00:00:00')->move(Duration::parse('-PT1S')),
            LocalDateTimeInterval::parse('2012-04-10T00:00:00/2012-12-31T23:59:59')
        );

        self::assertEquals(
            LocalDateTimeInterval::parse('2012-01-01T01:50/2012-01-01T23:50')->move(Duration::parse('PT20M')),
            LocalDateTimeInterval::parse('2012-01-01T02:10/2012-01-02T00:10')
        );

        self::assertEquals(
            LocalDateTimeInterval::parse('2012-01-01T10:00/2012-01-01T20:00')->move(Duration::parse('PT20H')),
            LocalDateTimeInterval::parse('2012-01-02T06:00/2012-01-02T16:00')
        );
    }

    public function testMoveWithPeriod(): void
    {
        //Period
        self::assertEquals(
            LocalDateTimeInterval::parse('2012-01-01T00:00/2012-01-02T00:00')->move(Period::parse('P1D')),
            LocalDateTimeInterval::parse('2012-01-02T00:00/2012-01-03T00:00')
        );

        self::assertEquals(
            LocalDateTimeInterval::parse('2012-01-01T00:00/2012-01-02T00:00')->move(Period::parse('P1W1D')),
            LocalDateTimeInterval::parse('2012-01-09T00:00/2012-01-10T00:00')
        );

        self::assertEquals(
            LocalDateTimeInterval::parse('2012-01-01T00:00/2012-01-02T00:00')->move(Period::parse('P1M1W1D')),
            LocalDateTimeInterval::parse('2012-02-09T00:00/2012-02-10T00:00')
        );

        self::assertEquals(
            LocalDateTimeInterval::parse('2012-02-09T00:00/2012-02-10T00:00')->move(Period::parse('-P1M1W1D')),
            LocalDateTimeInterval::parse('2012-01-01T00:00/2012-01-02T00:00')
        );

        //Leap year
        self::assertEquals(
            LocalDateTimeInterval::parse('2016-02-29T00:00/2016-06-01T00:00')->move(Period::parse('P1Y')),
            LocalDateTimeInterval::parse('2017-02-28T00:00/2017-06-01T00:00')
        );

        self::assertEquals(
            LocalDateTimeInterval::parse('2017-02-28T00:00/2017-06-01T00:00')->move(Period::parse('-P1Y')),
            LocalDateTimeInterval::parse('2016-02-28T00:00/2016-06-01T00:00')
        );

        self::assertEquals(
            LocalDateTimeInterval::parse('2016-02-29T00:00/2016-06-01T00:00')->move(Period::parse('-P1Y')),
            LocalDateTimeInterval::parse('2015-02-28T00:00/2015-06-01T00:00')
        );
    }

    /**
     * @dataProvider streamProvider
     */
    public function testStream(int $expectedCount, string $strPeriod, string $strDuration): void
    {
        $interval = LocalDateTimeInterval::parse('2016-01-01T00:00/2016-01-31T00:00');

        self::assertCount(
            $expectedCount,
            iterator_to_array(
                $interval->stream(
                    $strPeriod ? Period::parse($strPeriod) : null,
                    $strDuration ? Duration::parse($strDuration) : null
                )
            )
        );
    }

    public function streamProvider(): iterable
    {
        yield [30, 'P1D', 'PT1S'];
        yield [30, 'P1D', 'PT1M'];
        yield [29, 'P1D', 'PT1H'];
        yield [12, 'P1D', 'P1DT12H'];
        yield [30, '', 'P1D'];
        yield [30, '', 'PT24H'];
        yield [30, 'P1D', ''];
        yield [5, 'P7D', ''];
        yield [5, 'P1W', ''];
        yield [1, 'P30D', ''];
        yield [1, 'P1M', ''];
        yield [1, 'P1M1D', ''];
        yield [1, 'P2M', ''];
    }

    public function testWithStart(): void
    {
        $start = LocalDatetime::of(2014, 1, 1);
        $mid = LocalDatetime::of(2014, 1, 10);
        $end = LocalDatetime::of(2014, 1, 20);

        $interval = LocalDateTimeInterval::between($mid, $end);

        self::assertEquals(LocalDateTimeInterval::between($start, $end), $interval->withStart($start));
    }

    public function testWithEnd(): void
    {
        $start = LocalDatetime::of(2014, 1, 1);
        $mid = LocalDatetime::of(2014, 1, 10);
        $end = LocalDatetime::of(2014, 1, 20);

        $interval = LocalDateTimeInterval::between($start, $mid);

        self::assertEquals(LocalDateTimeInterval::between($start, $end), $interval->withEnd($end));
    }

    public function testSince(): void
    {
        $since = LocalDateTimeInterval::since(LocalDateTime::of(2016, 2, 28, 13, 20));

        self::assertTrue($since->hasInfiniteEnd());
    }

    public function testUntil(): void
    {
        $until = LocalDateTimeInterval::until(LocalDateTime::of(2016, 2, 28, 13, 20));

        self::assertTrue($until->hasInfiniteStart());
    }

    public function testEmpty(): void
    {
        $interval = LocalDateTimeInterval::between(
            LocalDateTime::of(2016, 2, 28, 13, 20),
            LocalDateTime::of(2016, 2, 28, 13, 20)
        );

        self::assertTrue($interval->isEmpty());
        self::assertFalse(LocalDateTimeInterval::since(LocalDateTime::of(2016, 2, 28, 13, 20))->isEmpty());
        self::assertFalse(LocalDateTimeInterval::until(LocalDateTime::of(2016, 2, 28, 13, 20))->isEmpty());
    }

    private function interval(string $i, string $strDuration = ''): LocalDateTimeInterval
    {
        $intervals = [
            '----|2010' => LocalDateTimeInterval::until(LocalDateTime::of(2010, 1, 1, 0, 0, 0)),
            '----|2011' => LocalDateTimeInterval::until(LocalDateTime::of(2011, 1, 1, 0, 0, 0)),
            '----|2012' => LocalDateTimeInterval::until(LocalDateTime::of(2012, 1, 1, 0, 0, 0)),
            '----|2013' => LocalDateTimeInterval::until(LocalDateTime::of(2013, 1, 1, 0, 0, 0)),
            '2010|2011' => LocalDateTimeInterval::parse('2010-01-01T00:00:00/2011-01-01T00:00:00'),
            '2010|2012' => LocalDateTimeInterval::parse('2010-01-01T00:00:00/2012-01-01T00:00:00'),
            '2010|2013' => LocalDateTimeInterval::parse('2010-01-01T00:00:00/2013-01-01T00:00:00'),
            '2011|2012' => LocalDateTimeInterval::parse('2011-01-01T00:00:00/2012-01-01T00:00:00'),
            '2011|2013' => LocalDateTimeInterval::parse('2011-01-01T00:00:00/2013-01-01T00:00:00'),
            '2012|2013' => LocalDateTimeInterval::parse('2012-01-01T00:00:00/2013-01-01T00:00:00'),
            '2010|----' => LocalDateTimeInterval::since(LocalDateTime::of(2010, 1, 1, 0, 0, 0)),
            '2011|----' => LocalDateTimeInterval::since(LocalDateTime::of(2011, 1, 1, 0, 0, 0)),
            '2012|----' => LocalDateTimeInterval::since(LocalDateTime::of(2012, 1, 1, 0, 0, 0)),
            '2013|----' => LocalDateTimeInterval::since(LocalDateTime::of(2013, 1, 1, 0, 0, 0)),
        ];

        return $strDuration ? $intervals[$i]->move(Duration::parse($strDuration)) : $intervals[$i];
    }

    private function temporal(string $i): LocalDateTime
    {
        $temporal =  [
            '2009'    => LocalDateTime::of(2009, 1, 1),
            '2010'    => LocalDateTime::of(2010, 1, 1),
            '2011'    => LocalDateTime::of(2011, 1, 1),
            '2012'    => LocalDateTime::of(2012, 1, 1),
            '2013'    => LocalDateTime::of(2013, 1, 1),
            '2014'    => LocalDateTime::of(2014, 1, 1),
        ];

        return $temporal[$i];
    }

    public function testIsBefore(): void
    {
        $_this = $this;

        self::assertTrue($_this->interval('----|2010')->isBefore($this->temporal('2011')));
        self::assertTrue($_this->interval('2010|2011')->isBefore($this->temporal('2011')));
        self::assertFalse($this->interval('2011|2012')->isBefore($this->temporal('2011')));
        self::assertFalse($this->interval('2012|----')->isBefore($this->temporal('2011')));
    }

    public function testIsBeforeInterval(): void
    {
        $_this = $this;
        self::assertTrue($_this->interval('----|2010')->isBeforeInterval($this->interval('2012|2013')));
        self::assertTrue($_this->interval('2010|2011')->isBeforeInterval($this->interval('2012|2013')));
        self::assertTrue($_this->interval('2011|2012')->isBeforeInterval($this->interval('2012|2013')));
        self::assertFalse($this->interval('2012|2013')->isBeforeInterval($this->interval('2012|2013')));
        self::assertFalse($this->interval('2013|----')->isBeforeInterval($this->interval('2012|2013')));
    }

    public function testIsAfter(): void
    {
        $_this = $this;

        self::assertFalse($this->interval('----|2010')->isAfter($this->temporal('2010')));
        self::assertFalse($this->interval('2010|2011')->isAfter($this->temporal('2010')));
        self::assertTrue($_this->interval('2011|2012')->isAfter($this->temporal('2010')));
        self::assertTrue($_this->interval('2012|----')->isAfter($this->temporal('2010')));
    }

    public function testIsAfterInterval(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('----|2010')->isAfterInterval($this->interval('2010|2011')));
        self::assertFalse($this->interval('2010|2011')->isAfterInterval($this->interval('2010|2011')));
        self::assertTrue($_this->interval('2011|2012')->isAfterInterval($this->interval('2010|2011')));
        self::assertTrue($_this->interval('2012|2013')->isAfterInterval($this->interval('2010|2011')));
        self::assertTrue($_this->interval('2013|----')->isAfterInterval($this->interval('2010|2011')));
    }

    public function testContains(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('----|2010')->contains($this->temporal('2010')));
        self::assertTrue($_this->interval('2010|2011')->contains($this->temporal('2010')));
        self::assertTrue($_this->interval('2010|----')->contains($this->temporal('2010')));
    }

    public function testContainsInterval(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('2010|2011')->containsInterval($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2010|2013')->containsInterval($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2010|2012')->containsInterval($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2011|2013')->containsInterval($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2011|2012')->containsInterval($this->interval('2011|2012')));
        self::assertFalse($this->interval('2012|2013')->containsInterval($this->interval('2011|2012')));

        self::assertTrue($_this->interval('----|2012')->containsInterval($this->interval('2010|2011')));
        self::assertTrue($_this->interval('----|2011')->containsInterval($this->interval('2010|2011')));
        self::assertTrue($_this->interval('2010|----')->containsInterval($this->interval('2010|2011')));
        self::assertFalse($this->interval('2011|----')->containsInterval($this->interval('2010|2011')));
    }

    public function testPrecedes(): void
    {
        $_this = $this;
        self::assertTrue($_this->interval('----|2010')->precedes($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2010|2011')->precedes($this->interval('2011|2012', 'PT1S')));
        self::assertFalse($this->interval('2010|2011')->precedes($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2012')->precedes($this->interval('2011|2012')));
        self::assertFalse($this->interval('2012|----')->precedes($this->interval('2011|2012')));
    }

    public function testPrecededBy(): void
    {
        $_this = $this;
        self::assertTrue($_this->interval('2011|2012')->precededBy($this->interval('----|2010')));
        self::assertTrue($_this->interval('2011|2012')->precededBy($this->interval('2010|2011', '-PT1S')));
        self::assertFalse($this->interval('2011|2012')->precededBy($this->interval('2010|2011')));
        self::assertFalse($this->interval('2011|2012')->precededBy($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2012')->precededBy($this->interval('2012|----')));
    }

    public function testMeets(): void
    {
        $_this = $this;
        self::assertTrue($_this->interval('----|2011')->meets($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2010|2011')->meets($this->interval('2011|2012')));
        self::assertFalse($this->interval('2010|2011')->meets($this->interval('2011|2012', 'PT1S')));
        self::assertFalse($this->interval('2011|2012')->meets($this->interval('2011|2012')));
        self::assertFalse($this->interval('2012|----')->meets($this->interval('2011|2012')));
    }

    public function testMetBy(): void
    {
        $_this = $this;
        self::assertTrue($this->interval('2011|2012')->metBy($_this->interval('----|2011')));
        self::assertTrue($this->interval('2011|2012')->metBy($_this->interval('2010|2011')));
        self::assertFalse($this->interval('2011|2012')->metBy($this->interval('2010|2011', '-PT1S')));
        self::assertFalse($this->interval('2011|2012')->metBy($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2012')->metBy($this->interval('2012|----')));
    }
}
