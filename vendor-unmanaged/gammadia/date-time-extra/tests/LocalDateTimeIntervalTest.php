<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;
use Gammadia\DateTimeExtra\InstantInterval;
use Gammadia\DateTimeExtra\IntervalParseException;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use Gammadia\DateTimeExtra\ZonedDateTimeInterval;
use PHPUnit\Framework\TestCase;
use function Gammadia\Collections\Functional\map;

class LocalDateTimeIntervalTest extends TestCase
{
    public function testToIsoString(): void
    {
        $start = LocalDateTime::parse('2007-01-01T10:15:30');
        $end = LocalDateTime::parse('2007-02-14T10:15:30');
        $interval = LocalDateTimeInterval::between($start, $end);

        self::assertSame('2007-01-01T10:15:30/2007-02-14T10:15:30', $interval->toString());
    }

    public function testToIsoStringInfinity(): void
    {
        $since = LocalDateTimeInterval::since(LocalDateTime::of(2016, 2, 28, 13, 20));
        $until = LocalDateTimeInterval::until(LocalDateTime::of(2016, 2, 28, 13, 20));

        self::assertSame('2016-02-28T13:20/-', $since->toString());
        self::assertSame('-/2016-02-28T13:20', $until->toString());
    }

    public function testInUTC(): void
    {
        $t1 = LocalDateTime::of(2014, 2, 27, 0, 0);
        $t2 = LocalDateTime::of(2014, 5, 14, 23, 59, 59);

        $m1 = $t1->atTimeZone(TimeZoneOffset::utc())->getInstant();
        $m2 = $t2->atTimeZone(TimeZoneOffset::utc())->getInstant();

        self::assertTrue(LocalDateTimeInterval::between($t1, $t2)->atUTC()->equals(InstantInterval::between($m1, $m2)));
    }

    public function testInTimezoneSaoPaulo(): void
    {
        $saoPaulo = TimeZoneRegion::of('America/Sao_Paulo');

        $ldt1 = LocalDateTime::of(2016, 10, 16, 1, 0);
        $ldt2 = LocalDateTime::of(2016, 10, 16, 2, 0);

        self::assertTrue(
            LocalDateTimeInterval::between($ldt1, $ldt2)->atTimeZone($saoPaulo)->equals(
                ZonedDateTimeInterval::between($ldt1->atTimeZone($saoPaulo), $ldt2->atTimeZone($saoPaulo))
            )
        );
    }

    public function testParseLocalDateTimeAndPeriod(): void
    {
        $start = LocalDateTime::of(2012, 4, 1, 14, 15);
        $end = LocalDateTime::of(2012, 4, 5, 16, 0);
        $expected = LocalDateTimeInterval::between($start, $end);

        self::assertTrue(LocalDateTimeInterval::parse('2012-04-01T14:15/P4DT1H45M')->isEqualTo($expected));
    }

    public function testParsePeriodAndLocalDateTime(): void
    {
        $start = LocalDateTime::of(2012, 4, 1, 14, 15);
        $end = LocalDateTime::of(2012, 4, 5, 16, 0);
        $expected = LocalDateTimeInterval::between($start, $end);

        self::assertTrue(LocalDateTimeInterval::parse('P4DT1H45M/2012-04-05T16:00')->isEqualTo($expected));
    }

    /**
     * @dataProvider providerParseInvalidIntervalsThrowsIntervalParseException
     */
    public function testParseInvalidStringThrowsIntervalParseException(string $text): void
    {
        $this->expectException(IntervalParseException::class);

        LocalDateTimeInterval::parse($text);
    }

    /**
     * @return string[][]
     */
    public function providerParseInvalidIntervalsThrowsIntervalParseException(): array
    {
        return [
            ['P4DT1H45M/P2DT1H45M'],
            ['-/P2DT1H45M'],
            ['P4DT1H45M/-'],
        ];
    }

    /**
     * @dataProvider providerParseInvalidIntervalsThrowsException
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
            ['2012-04-30T14:15/T16:00'],
            ['2012-04-30T14:15/T24:00'],
            ['2012-04-30T14:15/16:00'],
            ['2012-092T14:15/096T16:00'],
            ['2012-W13-7T14:15/W14-4T16:00'],
            ['2012092T1415/096T1600'],
            ['2012W137T1415/W144T1600'],
            ['2012-092T14:15/2012-096T16:00'],
            ['2012-W13-7T14:15/2012-W14-4T16:00'],
            ['2012092T1415/2012096T1600'],
            ['2012W137T1415/2012W144T1600'],
            ['2012-04-01T14:15/P2M4DT1H45M'],
            ['2012-092T14:15/P4DT1H45M'],
            ['2012-W13-7T14:15/P0000-00-04T01:45'],
            ['P4DT1H45M/2012-096T16:00'],
            ['P0000-00-04T01:45/2012-W14-4T16:00'],
            ['2015-01-01T08:45/+∞'],
            ['-∞/2015-01-01T08:45'],
            ['2015-01-01T08:45/+999999999-12-31T23:59:59,999999999'],
            ['-∞/+∞'],
        ];
    }

    public function testParseAlways(): void
    {
        self::assertTrue(LocalDateTimeInterval::parse('-/-')->isEqualTo(LocalDateTimeInterval::between(null, null)));
    }

    public function testParseInfinity(): void
    {
        $tsp = LocalDateTime::of(2015, 1, 1, 8, 45);

        self::assertTrue(
            LocalDateTimeInterval::parse('2015-01-01T08:45/-')->isEqualTo(LocalDateTimeInterval::since($tsp))
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('-/2015-01-01T08:45')->isEqualTo(LocalDateTimeInterval::until($tsp))
        );
    }

    public function testGetDurationOfLocalDateTimeIntervalWithZonalCorrection(): void
    {
        $interval =
            LocalDatetimeInterval::between(
                LocalDatetime::of(2014, 1, 1, 21, 45),
                LocalDatetime::of(2014, 1, 31, 7, 0)
            );

        self::assertTrue(Duration::parse('P29DT9H15M')->isEqualTo($interval->getDuration()));
    }

    public function testMoveWithDuration(): void
    {
        //Duration
        self::assertTrue(
            LocalDateTimeInterval::parse('2012-04-10T00:00:01/2013-01-01T00:00:00')
                ->isEqualTo(
                    LocalDateTimeInterval::parse('2012-04-10T00:00:00/2012-12-31T23:59:59')
                        ->move(Duration::parse('+PT1S'))
                )
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('2012-04-10T00:00:00/2012-12-31T23:59:59')
                ->isEqualTo(
                    LocalDateTimeInterval::parse('2012-04-10T00:00:01/2013-01-01T00:00:00')
                        ->move(Duration::parse('-PT1S'))
                )
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('2012-01-01T02:10/2012-01-02T00:10')
                ->isEqualTo(
                    LocalDateTimeInterval::parse('2012-01-01T01:50/2012-01-01T23:50')
                        ->move(Duration::parse('PT20M'))
                )
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('2012-01-02T06:00/2012-01-02T16:00')
                ->isEqualTo(
                    LocalDateTimeInterval::parse('2012-01-01T10:00/2012-01-01T20:00')
                        ->move(Duration::parse('PT20H'))
                )
        );
    }

    public function testMoveWithPeriod(): void
    {
        //Period
        self::assertTrue(
            LocalDateTimeInterval::parse('2012-01-02T00:00/2012-01-03T00:00')->isEqualTo(
                LocalDateTimeInterval::parse('2012-01-01T00:00/2012-01-02T00:00')->move(Period::parse('P1D'))
            )
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('2012-01-09T00:00/2012-01-10T00:00')->isEqualTo(
                LocalDateTimeInterval::parse('2012-01-01T00:00/2012-01-02T00:00')->move(Period::parse('P1W1D'))
            )
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('2012-02-09T00:00/2012-02-10T00:00')->isEqualTo(
                LocalDateTimeInterval::parse('2012-01-01T00:00/2012-01-02T00:00')->move(Period::parse('P1M1W1D'))
            )
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('2012-01-01T00:00/2012-01-02T00:00')->isEqualTo(
                LocalDateTimeInterval::parse('2012-02-09T00:00/2012-02-10T00:00')->move(Period::parse('-P1M1W1D'))
            )
        );

        //Leap year
        self::assertTrue(
            LocalDateTimeInterval::parse('2017-02-28T00:00/2017-06-01T00:00')->isEqualTo(
                LocalDateTimeInterval::parse('2016-02-29T00:00/2016-06-01T00:00')->move(Period::parse('P1Y'))
            )
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('2016-02-28T00:00/2016-06-01T00:00')->isEqualTo(
                LocalDateTimeInterval::parse('2017-02-28T00:00/2017-06-01T00:00')->move(Period::parse('-P1Y'))
            )
        );

        self::assertTrue(
            LocalDateTimeInterval::parse('2015-02-28T00:00/2015-06-01T00:00')->isEqualTo(
                LocalDateTimeInterval::parse('2016-02-29T00:00/2016-06-01T00:00')->move(Period::parse('-P1Y'))
            )
        );
    }

    /**
     * @dataProvider iterateProvider
     */
    public function testIterate(int $expectedCount, string $strPeriod, string $strDuration): void
    {
        $interval = LocalDateTimeInterval::parse('2016-01-01T00:00/2016-01-31T00:00');

        self::assertCount(
            $expectedCount,
            iterator_to_array(
                $interval->iterate(
                    $strPeriod ? Period::parse($strPeriod) : Duration::parse($strDuration)
                )
            )
        );
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function iterateProvider(): iterable
    {
        yield [30, 'P1D', ''];
        yield [30, '', 'P1D'];
        yield [720, '', 'PT1H'];
        yield [29, '', 'PT25H'];
        yield [20, '', 'P1DT12H'];
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

    /**
     * @dataProvider slicesCases
     *
     * @param Period|Duration $durationOrPeriod
     * @param string[] $expected
     */
    public function testSlices($durationOrPeriod, string $interval, array $expected): void
    {
        $interval = LocalDateTimeInterval::parse($interval);
        $slices = map(
            iterator_to_array($interval->slice($durationOrPeriod)),
            static function (LocalDateTimeInterval $slice): string {
                return $slice->toString();
            }
        );
        self::assertSame($expected, $slices);
    }

    /**
     * @return iterable<mixed[]>
     */
    public function slicesCases(): iterable
    {
        yield [
            Period::parse('P1D'),
            '2019-02-01T00:00:00/2019-02-03T00:00:00',
            [
                '2019-02-01T00:00/2019-02-02T00:00',
                '2019-02-02T00:00/2019-02-03T00:00',
            ],
        ];
        yield [
            Period::parse('P1D'),
            '2019-03-31T00:00:00/P3D',
            [
                '2019-03-31T00:00/2019-04-01T00:00',
                '2019-04-01T00:00/2019-04-02T00:00',
                '2019-04-02T00:00/2019-04-03T00:00',
            ],
        ];
        yield [
            Duration::parse('PT23H'),
            '2019-03-31T00:00:00/PT24H',
            [
                '2019-03-31T00:00/2019-03-31T23:00',
                '2019-03-31T23:00/2019-04-01T00:00',
            ],
        ];
        yield [
            Duration::parse('PT3H'),
            '2019-03-31T01:00:00/2019-04-01T00:00:00',
            [
                '2019-03-31T01:00/2019-03-31T04:00',
                '2019-03-31T04:00/2019-03-31T07:00',
                '2019-03-31T07:00/2019-03-31T10:00',
                '2019-03-31T10:00/2019-03-31T13:00',
                '2019-03-31T13:00/2019-03-31T16:00',
                '2019-03-31T16:00/2019-03-31T19:00',
                '2019-03-31T19:00/2019-03-31T22:00',
                '2019-03-31T22:00/2019-04-01T00:00',
            ],
        ];
    }

    public function testWithStart(): void
    {
        $start = LocalDatetime::of(2014, 1, 1);
        $mid = LocalDatetime::of(2014, 1, 10);
        $end = LocalDatetime::of(2014, 1, 20);

        $interval = LocalDateTimeInterval::between($mid, $end);

        self::assertTrue(LocalDateTimeInterval::between($start, $end)->isEqualTo($interval->withStart($start)));
    }

    public function testWithEnd(): void
    {
        $start = LocalDatetime::of(2014, 1, 1);
        $mid = LocalDatetime::of(2014, 1, 10);
        $end = LocalDatetime::of(2014, 1, 20);

        $interval = LocalDateTimeInterval::between($start, $mid);

        self::assertTrue(LocalDateTimeInterval::between($start, $end)->isEqualTo($interval->withEnd($end)));
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
            '----|2009' => LocalDateTimeInterval::until(LocalDateTime::of(2009, 1, 1, 0, 0, 0)),
            '----|2010' => LocalDateTimeInterval::until(LocalDateTime::of(2010, 1, 1, 0, 0, 0)),
            '----|2011' => LocalDateTimeInterval::until(LocalDateTime::of(2011, 1, 1, 0, 0, 0)),
            '----|2012' => LocalDateTimeInterval::until(LocalDateTime::of(2012, 1, 1, 0, 0, 0)),
            '----|2013' => LocalDateTimeInterval::until(LocalDateTime::of(2013, 1, 1, 0, 0, 0)),
            '----|2014' => LocalDateTimeInterval::until(LocalDateTime::of(2014, 1, 1, 0, 0, 0)),
            '2009|2010' => LocalDateTimeInterval::parse('2009-01-01T00:00:00/2010-01-01T00:00:00'),
            '2009|2011' => LocalDateTimeInterval::parse('2009-01-01T00:00:00/2011-01-01T00:00:00'),
            '2009|2012' => LocalDateTimeInterval::parse('2009-01-01T00:00:00/2012-01-01T00:00:00'),
            '2009|2013' => LocalDateTimeInterval::parse('2009-01-01T00:00:00/2013-01-01T00:00:00'),
            '2010|2011' => LocalDateTimeInterval::parse('2010-01-01T00:00:00/2011-01-01T00:00:00'),
            '2010|2012' => LocalDateTimeInterval::parse('2010-01-01T00:00:00/2012-01-01T00:00:00'),
            '2010|2013' => LocalDateTimeInterval::parse('2010-01-01T00:00:00/2013-01-01T00:00:00'),
            '2011|2011' => LocalDateTimeInterval::parse('2011-01-01T00:00:00/2011-01-01T00:00:00'),
            '2011|2012' => LocalDateTimeInterval::parse('2011-01-01T00:00:00/2012-01-01T00:00:00'),
            '2011|2014' => LocalDateTimeInterval::parse('2011-01-01T00:00:00/2014-01-01T00:00:00'),
            '2011|2013' => LocalDateTimeInterval::parse('2011-01-01T00:00:00/2013-01-01T00:00:00'),
            '2012|2013' => LocalDateTimeInterval::parse('2012-01-01T00:00:00/2013-01-01T00:00:00'),
            '2013|2014' => LocalDateTimeInterval::parse('2013-01-01T00:00:00/2014-01-01T00:00:00'),
            '2009|----' => LocalDateTimeInterval::since(LocalDateTime::of(2009, 1, 1, 0, 0, 0)),
            '2010|----' => LocalDateTimeInterval::since(LocalDateTime::of(2010, 1, 1, 0, 0, 0)),
            '2011|----' => LocalDateTimeInterval::since(LocalDateTime::of(2011, 1, 1, 0, 0, 0)),
            '2012|----' => LocalDateTimeInterval::since(LocalDateTime::of(2012, 1, 1, 0, 0, 0)),
            '2013|----' => LocalDateTimeInterval::since(LocalDateTime::of(2013, 1, 1, 0, 0, 0)),
        ];

        return $strDuration ? $intervals[$i]->move(Duration::parse($strDuration)) : $intervals[$i];
    }

    private function temporal(string $i): LocalDateTime
    {
        $temporal = [
            '2009' => LocalDateTime::of(2009, 1, 1),
            '2010' => LocalDateTime::of(2010, 1, 1),
            '2011' => LocalDateTime::of(2011, 1, 1),
            '2012' => LocalDateTime::of(2012, 1, 1),
            '2013' => LocalDateTime::of(2013, 1, 1),
            '2014' => LocalDateTime::of(2014, 1, 1),
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
        self::assertFalse($this->interval('----|2012')->containsInterval($this->interval('----|2011')));
        self::assertTrue($_this->interval('----|2012')->containsInterval($this->interval('2010|2011')));
        self::assertTrue($_this->interval('----|2011')->containsInterval($this->interval('2010|2011')));
        self::assertTrue($_this->interval('2010|----')->containsInterval($this->interval('2010|2011')));
        self::assertFalse($this->interval('2011|----')->containsInterval($this->interval('2010|2011')));
    }

    public function testPrecedes(): void
    {
        $_this = $this;
        self::assertTrue($_this->interval('----|2010')->precedes($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2010|2011')->precedes($this->interval('2011|2012', '+PT1S')));
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
        self::assertFalse($this->interval('2010|2011')->meets($this->interval('2011|2012', '+PT1S')));
        self::assertFalse($this->interval('2011|2012')->meets($this->interval('2011|2012')));
        self::assertFalse($this->interval('2012|----')->meets($this->interval('2011|2012')));
    }

    public function testMetBy(): void
    {
        $_this = $this;
        self::assertTrue($_this->interval('2011|2012')->metBy($this->interval('----|2011')));
        self::assertTrue($_this->interval('2011|2012')->metBy($this->interval('2010|2011')));
        self::assertFalse($this->interval('2011|2012')->metBy($this->interval('2010|2011', '-PT1S')));
        self::assertFalse($this->interval('2011|2012')->metBy($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2012')->metBy($this->interval('2012|----')));
    }

    public function testIntersects(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('----|2010')->intersects($this->interval('2010|2013')));
        self::assertTrue($_this->interval('----|2010')->intersects($this->interval('2010|2013', '-PT1S')));
        self::assertFalse($this->interval('2009|2010')->intersects($this->interval('2010|2013')));
        self::assertTrue($_this->interval('2009|2010')->intersects($this->interval('2010|2013', '-PT1S')));
        self::assertTrue($_this->interval('2010|2011')->intersects($this->interval('2010|2013')));
        self::assertTrue($_this->interval('2011|2012')->intersects($this->interval('2010|2013')));
        self::assertTrue($_this->interval('2011|2014')->intersects($this->interval('2010|2013')));
        self::assertFalse($this->interval('2013|2014')->intersects($this->interval('2010|2013')));
        self::assertTrue($_this->interval('2013|2014')->intersects($this->interval('2010|2013', '+PT1S')));
        self::assertFalse($this->interval('2013|----')->intersects($this->interval('2010|2013')));
        self::assertTrue($_this->interval('2013|----')->intersects($this->interval('2010|2013', '+PT1S')));
    }

    public function testFindIntersection(): void
    {
        self::assertNull($this->interval('2009|2010')->findIntersection($this->interval('2010|2013')));
        self::assertNull($this->interval('2013|2014')->findIntersection($this->interval('2010|2013')));
        self::assertNull($this->interval('2013|----')->findIntersection($this->interval('2010|2013')));
        self::assertNull($this->interval('----|2010')->findIntersection($this->interval('2010|2013')));

        $intersection = $this->interval('2009|2011')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection && $this->interval('2010|2011')->isEqualTo($intersection));

        $intersection2 = $this->interval('2010|2011')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection2 && $this->interval('2010|2011')->isEqualTo($intersection2));

        $intersection3 = $this->interval('2011|2012')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection3 && $this->interval('2011|2012')->isEqualTo($intersection3));

        $intersection4 = $this->interval('2011|2014')->findIntersection($this->interval('2010|2013'));
        self::assertTrue($intersection4 && $this->interval('2011|2013')->isEqualTo($intersection4));
    }

    public function testFinishes(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('----|2013')->finishes($this->interval('2010|2013')));
        self::assertFalse($this->interval('2009|2013')->finishes($this->interval('2010|2013')));
        self::assertFalse($this->interval('2010|2013')->finishes($this->interval('2010|2013')));
        self::assertFalse($this->interval('2010|2011')->finishes($this->interval('2010|2013')));
        self::assertFalse($this->interval('2010|----')->finishes($this->interval('2010|2013')));
        self::assertFalse($this->interval('2010|----')->finishes($this->interval('2010|----')));
        self::assertTrue($_this->interval('2013|----')->finishes($this->interval('2010|----')));
        self::assertTrue($_this->interval('2011|2013')->finishes($this->interval('2010|2013')));
        self::assertTrue($_this->interval('2011|2013')->finishes($this->interval('----|2013')));
    }

    public function testFinishedBy(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('2010|2013')->finishedBy($this->interval('----|2013')));
        self::assertFalse($this->interval('2010|2013')->finishedBy($this->interval('2009|2013')));
        self::assertFalse($this->interval('2010|2013')->finishedBy($this->interval('2010|2013')));
        self::assertFalse($this->interval('2010|2013')->finishedBy($this->interval('2010|2011')));
        self::assertFalse($this->interval('2010|2013')->finishedBy($this->interval('2010|----')));
        self::assertFalse($this->interval('2010|----')->finishedBy($this->interval('2010|----')));
        self::assertTrue($_this->interval('2010|2013')->finishedBy($this->interval('2011|2013')));
        self::assertTrue($_this->interval('----|2013')->finishedBy($this->interval('2011|2013')));
    }

    public function testStarts(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('2011|----')->starts($this->interval('2011|2013')));
        self::assertFalse($this->interval('2011|2014')->starts($this->interval('2011|2013')));
        self::assertFalse($this->interval('2011|2013')->starts($this->interval('2011|2013')));
        self::assertTrue($_this->interval('2011|2012')->starts($this->interval('2011|2013')));
        self::assertTrue($_this->interval('2011|2012')->starts($this->interval('2011|----')));
        self::assertFalse($this->interval('2011|----')->starts($this->interval('2011|----')));
        self::assertTrue($_this->interval('----|2010')->starts($this->interval('----|2013')));
        self::assertFalse($this->interval('----|2013')->starts($this->interval('----|2013')));
    }

    public function testStartedBy(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('2011|2013')->startedBy($this->interval('2011|----')));
        self::assertFalse($this->interval('2011|2013')->startedBy($this->interval('2011|2014')));
        self::assertFalse($this->interval('2011|2013')->startedBy($this->interval('2011|2013')));
        self::assertTrue($_this->interval('2011|2013')->startedBy($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2011|----')->startedBy($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|----')->startedBy($this->interval('2011|----')));
        self::assertTrue($_this->interval('----|2013')->startedBy($this->interval('----|2010')));
        self::assertFalse($this->interval('----|2013')->startedBy($this->interval('----|2013')));
    }

    public function testEncloses(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('2010|2011')->encloses($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2010|2013')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('2010|2012')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2013')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2012')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('2012|2013')->encloses($this->interval('2011|2012')));
        self::assertFalse($this->interval('----|2012')->encloses($this->interval('----|2011')));
        self::assertTrue($_this->interval('----|2012')->encloses($this->interval('2010|2011')));
        self::assertFalse($this->interval('----|2011')->encloses($this->interval('2010|2011')));
        self::assertFalse($this->interval('2010|----')->encloses($this->interval('2010|2011')));
        self::assertTrue($_this->interval('2010|----')->encloses($this->interval('2010|2011', '+PT1S')));
        self::assertTrue($_this->interval('2009|----')->encloses($this->interval('2010|2011')));
    }

    public function testEnclosedBy(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2010|2011')));
        self::assertTrue($_this->interval('2011|2012')->enclosedBy($this->interval('2010|2013')));
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2010|2012')));
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2011|2013')));
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2011|2012')));
        self::assertFalse($this->interval('2011|2012')->enclosedBy($this->interval('2012|2013')));
        self::assertFalse($this->interval('----|2011')->enclosedBy($this->interval('----|2012')));
        self::assertTrue($_this->interval('2010|2011')->enclosedBy($this->interval('----|2012')));
        self::assertFalse($this->interval('2010|2011')->enclosedBy($this->interval('----|2011')));
        self::assertFalse($this->interval('2010|2011')->enclosedBy($this->interval('2010|----')));
        self::assertTrue($_this->interval('2010|2011')->enclosedBy($this->interval('2009|----')));
    }

    public function testOverlaps(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('----|2010')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('----|2010')->overlaps($this->interval('----|2013')));
        self::assertFalse($this->interval('2009|2010')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2013|2014')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2010|2011')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2011|2012')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2011|2014')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2013|----')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2010|----')->overlaps($this->interval('2011|----')));
        self::assertFalse($this->interval('2013|2014')->overlaps($this->interval('2010|2013')));
        self::assertFalse($this->interval('2013|----')->overlaps($this->interval('2010|2013')));

        self::assertTrue($_this->interval('----|2010')->overlaps($this->interval('2010|2013', '-PT1S')));
        self::assertTrue($_this->interval('2009|2010')->overlaps($this->interval('2010|2013', '-PT1S')));
    }

    public function testOverlappedBy(): void
    {
        $_this = $this;
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('----|2010')));
        self::assertFalse($this->interval('----|2013')->overlappedBy($this->interval('----|2010')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2009|2010')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2013|2014')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2010|2011')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2011|2012')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2011|2014')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2013|----')));
        self::assertFalse($this->interval('2011|----')->overlappedBy($this->interval('2010|----')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2013|2014')));
        self::assertFalse($this->interval('2010|2013')->overlappedBy($this->interval('2013|----')));

        self::assertTrue($_this->interval('2010|2013', '-PT1S')->overlappedBy($this->interval('----|2010')));
        self::assertTrue($_this->interval('2010|2013', '-PT1S')->overlappedBy($this->interval('2009|2010')));
    }

    public function testAbuts(): void
    {
        $_this = $this;
        self::assertTrue($_this->interval('----|2011')->abuts($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2010|2011')->abuts($this->interval('2011|2012')));
        self::assertFalse($this->interval('2010|2011')->abuts($this->interval('2011|2012', '+PT1S')));
        self::assertFalse($this->interval('2011|2012')->abuts($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2012|----')->abuts($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2012|2013')->abuts($this->interval('2011|2012')));
        self::assertTrue($_this->interval('2012|----')->abuts($this->interval('2011|2012')));
    }

    public function testCollapse(): void
    {
        self::assertTrue($this->interval('2011|2011')->isEqualTo($this->interval('2011|2012')->collapse()));
        self::assertTrue($this->interval('2011|2011')->isEqualTo($this->interval('2011|----')->collapse()));
        self::assertTrue($this->interval('2011|2011')->isEqualTo($this->interval('2011|2011')->collapse()));
    }

    public function testEquals(): void
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
}
