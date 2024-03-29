<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Period;
use Gammadia\DateTimeExtra\Exceptions\IntervalParseException;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Gammadia\Collections\Functional\map;

final class LocalDateTimeIntervalTest extends TestCase
{
    public function testConstructThrowsInvalidArgumentExceptionInversedRanges(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Start after end: 2021-01-01T12:00 / 2021-01-01T11:30:24');

        LocalDateTimeInterval::between(LocalDateTime::parse('2021-01-01T12:00'), LocalDateTime::parse('2021-01-01T11:30:24'));
    }

    public function testToIsoString(): void
    {
        $start = LocalDateTime::parse('2007-01-01T10:15:30');
        $end = LocalDateTime::parse('2007-02-14T10:15:30');
        $interval = LocalDateTimeInterval::between($start, $end);

        self::assertSame('2007-01-01T10:15:30/2007-02-14T10:15:30', $interval->toString());
    }

    public function testToIsoStringInfinity(): void
    {
        $iso = '2016-02-28T13:20';
        $date = LocalDateTime::parse($iso);

        self::assertSame($iso . '/-', (string) LocalDateTimeInterval::since($date));
        self::assertSame('-/' . $iso, (string) LocalDateTimeInterval::until($date));
        self::assertSame('-/-', (string) LocalDateTimeInterval::forever());
    }

    public function testDay(): void
    {
        self::assertSame(
            '2020-01-01T00:00/2020-01-02T00:00',
            (string) LocalDateTimeInterval::day(LocalDate::parse('2020-01-01')),
        );
        self::assertSame(
            '2020-01-01T00:00/2020-01-02T00:00',
            (string) LocalDateTimeInterval::day(LocalDateTime::parse('2020-01-01T12:00:00')),
        );
    }

    public function testParseLocalDateTimeAndPeriod(): void
    {
        $iso = '2012-04-01T14:15/2012-04-05T16:00';

        self::assertSame($iso, (string) LocalDateTimeInterval::parse('2012-04-01T14:15/P4DT1H45M'));
        self::assertSame($iso, (string) LocalDateTimeInterval::parse('P4DT1H45M/2012-04-05T16:00'));
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
     * @return iterable<mixed>
     */
    public function providerParseInvalidIntervalsThrowsIntervalParseException(): iterable
    {
        yield ['P4DT1H45M/P2DT1H45M'];
        yield ['-/P2DT1H45M'];
        yield ['P4DT1H45M/-'];
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
     * @return iterable<mixed>
     */
    public function providerParseInvalidIntervalsThrowsException(): iterable
    {
        yield ['2012-04-30T14:15/T16:00'];
        yield ['2012-04-30T14:15/T24:00'];
        yield ['2012-04-30T14:15/16:00'];
        yield ['2012-092T14:15/096T16:00'];
        yield ['2012-W13-7T14:15/W14-4T16:00'];
        yield ['2012092T1415/096T1600'];
        yield ['2012W137T1415/W144T1600'];
        yield ['2012-092T14:15/2012-096T16:00'];
        yield ['2012-W13-7T14:15/2012-W14-4T16:00'];
        yield ['2012092T1415/2012096T1600'];
        yield ['2012W137T1415/2012W144T1600'];
        yield ['2012-04-01T14:15/P2M4DT1H45M'];
        yield ['2012-092T14:15/P4DT1H45M'];
        yield ['2012-W13-7T14:15/P0000-00-04T01:45'];
        yield ['P4DT1H45M/2012-096T16:00'];
        yield ['P0000-00-04T01:45/2012-W14-4T16:00'];
        yield ['2015-01-01T08:45/+∞'];
        yield ['-∞/2015-01-01T08:45'];
        yield ['2015-01-01T08:45/+999999999-12-31T23:59:59,999999999'];
        yield ['-∞/+∞'];
    }

    public function testParseInfinity(): void
    {
        $timepoint = '2015-01-01T08:45';

        self::assertSame('2015-01-01T08:45/-', (string) LocalDateTimeInterval::parse($timepoint . '/-'));
        self::assertSame('-/2015-01-01T08:45', (string) LocalDateTimeInterval::parse('-/' . $timepoint));
        self::assertSame('-/-', (string) LocalDateTimeInterval::forever());
    }

    public function testGetDurationOfLocalDateTimeIntervalWithZonalCorrection(): void
    {
        self::assertSame(
            (string) Duration::parse('P29DT9H15M'),
            (string) LocalDateTimeInterval::parse('2014-01-01T21:45/2014-01-31T07:00')->getDuration(),
        );
    }

    /**
     * @dataProvider move
     */
    public function testMoveWithDuration(string $iso, Duration|Period $input, string $expected): void
    {
        self::assertSame($expected, (string) LocalDateTimeInterval::parse($iso)->move($input));
    }

    /**
     * @return iterable<mixed>
     */
    public function move(): iterable
    {
        yield ['2012-04-10T00:00:00/2012-12-31T23:59:59', Duration::parse('+PT1S'), '2012-04-10T00:00:01/2013-01-01T00:00'];
        yield ['2012-04-10T00:00:01/2013-01-01T00:00', Duration::parse('-PT1S'), '2012-04-10T00:00/2012-12-31T23:59:59'];

        yield ['2012-01-01T01:50/2012-01-01T23:50', Duration::parse('PT20M'), '2012-01-01T02:10/2012-01-02T00:10'];
        yield ['2012-01-01T10:00/2012-01-01T20:00', Duration::parse('PT20H'), '2012-01-02T06:00/2012-01-02T16:00'];

        yield ['2012-01-01T00:00/2012-01-02T00:00', Period::parse('P1D'), '2012-01-02T00:00/2012-01-03T00:00'];
        yield ['2012-01-01T00:00/2012-01-02T00:00', Period::parse('P1W1D'), '2012-01-09T00:00/2012-01-10T00:00'];
        yield ['2012-01-01T00:00/2012-01-02T00:00', Period::parse('P1M1W1D'), '2012-02-09T00:00/2012-02-10T00:00'];
        yield ['2012-02-09T00:00/2012-02-10T00:00', Period::parse('-P1M1W1D'), '2012-01-01T00:00/2012-01-02T00:00'];

        // Leap year
        yield ['2016-02-29T00:00/2016-06-01T00:00', Period::parse('P1Y'), '2017-02-28T00:00/2017-06-01T00:00'];
        yield ['2017-02-28T00:00/2017-06-01T00:00', Period::parse('-P1Y'), '2016-02-28T00:00/2016-06-01T00:00'];
        yield ['2016-02-29T00:00/2016-06-01T00:00', Period::parse('-P1Y'), '2015-02-28T00:00/2015-06-01T00:00'];
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
                    $strPeriod ? Period::parse($strPeriod) : Duration::parse($strDuration),
                ),
            ),
        );
    }

    /**
     * @return iterable<mixed>
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
     * @param string[] $expected
     */
    public function testSlices(Duration|Period $durationOrPeriod, string $interval, array $expected): void
    {
        $interval = LocalDateTimeInterval::parse($interval);
        $slices = map(
            iterator_to_array($interval->slice($durationOrPeriod)),
            static fn (LocalDateTimeInterval $slice): string => $slice->toString(),
        );
        self::assertSame($expected, $slices);
    }

    /**
     * @return iterable<mixed>
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
        $start = '2014-01-01T00:00';
        $end = '2014-01-20T00:00';

        self::assertSame(
            $start . '/' . $end,
            (string) LocalDateTimeInterval::parse('2014-01-10T00:00/' . $end)->withStart(LocalDateTime::parse($start)),
        );
    }

    public function testWithEnd(): void
    {
        $start = '2014-01-01T00:00';
        $end = '2014-01-20T00:00';

        self::assertSame(
            $start . '/' . $end,
            (string) LocalDateTimeInterval::parse($start . '/2014-01-10T00:00')->withEnd(LocalDateTime::parse($end)),
        );
    }

    public function testSince(): void
    {
        self::assertSame('2016-02-28T13:20/-', (string) LocalDateTimeInterval::since(LocalDateTime::parse('2016-02-28T13:20')));
    }

    public function testUntil(): void
    {
        self::assertSame('-/2016-02-28T13:20', (string) LocalDateTimeInterval::until(LocalDateTime::parse('2016-02-28T13:20')));
    }

    public function testEmpty(): void
    {
        $interval = LocalDateTimeInterval::empty(LocalDateTime::parse('2016-02-28T13:20'));

        self::assertSame('2016-02-28T13:20/2016-02-28T13:20', (string) $interval);
        self::assertTrue($interval->isEmpty());
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

    /**
     * @dataProvider intersects
     */
    public function testIntersectsWithIsoStrings(string $a, string $b, bool $expected): void
    {
        self::assertSame($expected, LocalDateTimeInterval::parse($a)->intersects(LocalDateTimeInterval::parse($b)));
    }

    /**
     * @return iterable<mixed>
     */
    public function intersects(): iterable
    {
        $timeRange = '2020-01-02T14:00/2020-01-02T18:00';

        yield 'Same range' => [$timeRange, $timeRange, true];

        yield 'Starting before, ending at range start' => [$timeRange, '2020-01-02T12:00/2020-01-02T14:00', false];
        yield 'Starting before, ending in range' => [$timeRange, '2020-01-02T12:00/2020-01-02T17:00', true];
        yield 'Starting before, ending at range end' => [$timeRange, '2020-01-02T12:00/2020-01-02T18:00', true];
        yield 'Starting before, ending after range' => [$timeRange, '2020-01-02T12:00/2020-01-02T20:00', true];

        yield 'Starting in, ending in range' => [$timeRange, '2020-01-02T16:00/2020-01-02T17:00', true];
        yield 'Starting in, ending at range end' => [$timeRange, '2020-01-02T16:00/2020-01-02T18:00', true];
        yield 'Starting in, ending after range' => [$timeRange, '2020-01-02T15:00/2020-01-02T20:00', true];

        yield 'Starting exactly at range end' => [$timeRange, '2020-01-02T18:00/2020-01-02T20:00', false];

        yield 'Range contains empty range: before range' => [$timeRange, '2020-01-02T12:00/2020-01-02T12:00', false];
        yield 'Range contains empty range: exactly at range start' => [$timeRange, '2020-01-02T14:00/2020-01-02T14:00', true];
        yield 'Range contains empty range: in range' => [$timeRange, '2020-01-02T16:00/2020-01-02T16:00', true];
        yield 'Range contains empty range: exactly at range end' => [$timeRange, '2020-01-02T18:00/2020-01-02T18:00', false];
        yield 'Range contains empty range: after range' => [$timeRange, '2020-01-02T20:00/2020-01-02T20:00', false];

        yield 'Empty range contains range: before range' => ['2020-01-02T12:00/2020-01-02T12:00', $timeRange, false];
        yield 'Empty range contains range: exactly at range start' => ['2020-01-02T14:00/2020-01-02T14:00', $timeRange, true];
        yield 'Empty range contains range: in range' => ['2020-01-02T16:00/2020-01-02T16:00', $timeRange, true];
        yield 'Empty range contains range: exactly at range end' => ['2020-01-02T18:00/2020-01-02T18:00', $timeRange, false];
        yield 'Empty range contains range: after range' => ['2020-01-02T20:00/2020-01-02T20:00', $timeRange, false];
    }

    public function testFindIntersection(): void
    {
        self::assertNull($this->interval('2009|2010')->findIntersection($this->interval('2010|2013')));
        self::assertNull($this->interval('2013|2014')->findIntersection($this->interval('2010|2013')));
        self::assertNull($this->interval('2013|----')->findIntersection($this->interval('2010|2013')));
        self::assertNull($this->interval('----|2010')->findIntersection($this->interval('2010|2013')));
        self::assertNull($this->interval('----|2010')->findIntersection($this->interval('2010|2010')));
        self::assertNull($this->interval('2010|2010')->findIntersection($this->interval('2011|2011')));

        self::assertSame('2010-01-01T00:00/2010-01-01T00:00', (string) $this->interval('2010|----')->findIntersection($this->interval('2010|2010')));
        self::assertSame('2010-01-01T00:00/2011-01-01T00:00', (string) $this->interval('2009|2011')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2010-01-01T00:00/2011-01-01T00:00', (string) $this->interval('2010|2011')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2011-01-01T00:00/2012-01-01T00:00', (string) $this->interval('2011|2012')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2011-01-01T00:00/2013-01-01T00:00', (string) $this->interval('2011|2014')->findIntersection($this->interval('2010|2013')));
        self::assertSame('2011-01-01T00:00/2012-01-01T00:00', (string) $this->interval('----|2014')->findIntersection($this->interval('2011|2012')));
        self::assertSame('2011-01-01T00:00/2011-01-01T00:00', (string) $this->interval('----|2014')->findIntersection($this->interval('2011|2011')));
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

    /**
     * @dataProvider overlaps
     */
    public function testOverlapsWithIsoStrings(string $a, string $b, bool $expected): void
    {
        self::assertSame($expected, LocalDateTimeInterval::parse($a)->overlaps(LocalDateTimeInterval::parse($b)));
    }

    /**
     * @return iterable<mixed>
     */
    public function overlaps(): iterable
    {
        $timeRange = '2020-01-02T14:00/2020-01-02T18:00';
        yield 'overlaps = starts before + finishes within' => ['2020-01-02T08:00/2020-01-02T16:00', $timeRange, true];

        yield 'precedes' => ['2020-01-02T08:00/2020-01-02T12:00', $timeRange, false];
        yield 'meets' => ['2020-01-02T08:00/2020-01-02T14:00', $timeRange, false];
        yield 'encloses' => ['2020-01-02T08:00/2020-01-02T20:00', $timeRange, false];
        yield 'metBy' => ['2020-01-02T18:00/2020-01-02T20:00', $timeRange, false];
        yield 'precededBy' => ['2020-01-02T20:00/2020-01-02T22:00', $timeRange, false];
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
        $timepoint = LocalDateTime::parse('2011-01-01T00:00');
        $iso = (string) LocalDateTimeInterval::empty($timepoint);

        self::assertSame($iso, (string) LocalDateTimeInterval::parse('2011-01-01T00:00/2012-01-01T00:00')->collapse());
        self::assertSame($iso, (string) LocalDateTimeInterval::since($timepoint)->collapse());
        self::assertSame($iso, (string) LocalDateTimeInterval::empty($timepoint)->collapse());
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

    /**
     * @dataProvider containerOf
     *
     * @param string[] $input
     */
    public function testContainerOf(array $input, string $expected): void
    {
        self::assertSame(
            $expected,
            (string) LocalDateTimeInterval::containerOf(...map($input, static fn (string $timeRange): LocalDateTimeInterval
                => LocalDateTimeInterval::parse($timeRange),
            )),
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function containerOf(): iterable
    {
        // Same same
        yield [
            [
                '2020-01-01T00:00/2020-01-02T00:00',
            ],
            '2020-01-01T00:00/2020-01-02T00:00',
        ];

        // Consecutive time ranges
        yield [
            [
                '2020-01-01T00:00/2020-01-02T00:00',
                '2020-01-02T00:00/2020-01-03T00:00',
            ],
            '2020-01-01T00:00/2020-01-03T00:00',
        ];

        // With blanks
        yield [
            [
                '2020-01-01T12:00/2020-01-01T18:00',
                '2020-01-03T08:00/2020-01-04T00:00',
                '2020-01-04T00:00/2020-01-04T00:01',
            ],
            '2020-01-01T12:00/2020-01-04T00:01',
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
            (string) LocalDateTimeInterval::parse($iso)->expand(
                ...map($others, static fn (?string $timeRange): ?LocalDateTimeInterval
                    => null !== $timeRange ? LocalDateTimeInterval::parse($timeRange) : null,
                ),
            ),
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function expand(): iterable
    {
        $iso = '2020-01-02T08:00/2020-01-02T12:00';

        // Not actually expanding anything
        yield 'Empty others yield same range' => [$iso, [], $iso];
        yield 'Empty others because of null values yield same range' => [$iso, [null], $iso];
        yield 'Nulls mixed with ranges are skipped' => [$iso, [null, '2020-01-02T10:00/2020-01-02T12:00', null], $iso];

        // Expanding
        yield 'Expanding start (ends before range, finite)' => [$iso, ['2020-01-02T07:00/2020-01-02T07:30'], '2020-01-02T07:00/2020-01-02T12:00'];
        yield 'Expanding start (ends before range, infinite)' => [$iso, ['-/2020-01-02T07:30'], '-/2020-01-02T12:00'];
        yield 'Expanding start (ends in range, finite)' => [$iso, ['2020-01-02T07:00/2020-01-02T09:30'], '2020-01-02T07:00/2020-01-02T12:00'];
        yield 'Expanding start (ends in range, infinite)' => [$iso, ['-/2020-01-02T09:30'], '-/2020-01-02T12:00'];

        yield 'Expanding end (starts in range, finite)' => [$iso, ['2020-01-02T11:00/2020-01-02T14:30'], '2020-01-02T08:00/2020-01-02T14:30'];
        yield 'Expanding end (starts in range, infinite)' => [$iso, ['2020-01-02T11:00/-'], '2020-01-02T08:00/-'];
        yield 'Expanding end (starts after range, finite)' => [$iso, ['2020-01-02T14:30/2020-01-02T19:00'], '2020-01-02T08:00/2020-01-02T19:00'];
        yield 'Expanding end (starts after range, infinite)' => [$iso, ['2020-01-02T14:30/-'], '2020-01-02T08:00/-'];

        yield 'Expanding both (finite)' => [$iso, ['2020-01-02T07:00/2020-01-02T14:30'], '2020-01-02T07:00/2020-01-02T14:30'];
        yield 'Expanding both (infinite)' => [$iso, ['-/-'], '-/-'];

        yield 'Expand from multiple ranges' => [
            $iso,
            [
                '2020-01-02T07:00/2020-01-02T07:30',
                '2020-01-02T11:00/2020-01-02T11:30',
                '2020-01-02T18:00/2020-01-03T08:00',
            ],
            '2020-01-02T07:00/2020-01-03T08:00',
        ];
    }

    /**
     * @dataProvider toFullDays
     */
    public function testToFullDays(string $input, string $expected): void
    {
        self::assertSame($expected, (string) LocalDateTimeInterval::parse($input)->toFullDays());
    }

    /**
     * @return iterable<mixed>
     */
    public function toFullDays(): iterable
    {
        // Empty set
        yield 'Empty at midnight' => ['2020-01-01T00:00/2020-01-01T00:00', '2020-01-01T00:00/2020-01-02T00:00'];
        yield 'Empty at noon' => ['2020-01-01T12:00/2020-01-01T12:00', '2020-01-01T00:00/2020-01-02T00:00'];

        // Same day
        yield 'Midnight to midnight interval' => ['2020-01-01T00:00/2020-01-02T00:00', '2020-01-01T00:00/2020-01-02T00:00'];
        yield 'Hours to midnight interval' => ['2020-01-01T01:00/2020-01-02T00:00', '2020-01-01T00:00/2020-01-02T00:00'];
        yield 'Midnight to hours interval' => ['2020-01-01T00:00/2020-01-01T12:00', '2020-01-01T00:00/2020-01-02T00:00'];
        yield 'Hours to hours interval' => ['2020-01-01T08:00/2020-01-01T12:00', '2020-01-01T00:00/2020-01-02T00:00'];

        // Over multiple days
        yield 'Midnight to midnight next day interval' => ['2020-01-01T00:00/2020-01-03T00:00', '2020-01-01T00:00/2020-01-03T00:00'];
        yield 'Hours to midnight next day interval' => ['2020-01-01T01:00/2020-01-03T00:00', '2020-01-01T00:00/2020-01-03T00:00'];
        yield 'Midnight to hours next day interval' => ['2020-01-01T00:00/2020-01-02T12:00', '2020-01-01T00:00/2020-01-03T00:00'];
        yield 'Hours to hours next day interval' => ['2020-01-01T08:00/2020-01-02T12:00', '2020-01-01T00:00/2020-01-03T00:00'];

        // Infinites
        yield 'Infinite start ending at midnight' => ['-/2020-01-01T00:00', '-/2020-01-01T00:00'];
        yield 'Infinite start ending at hours' => ['-/2020-01-01T12:00', '-/2020-01-02T00:00'];
        yield 'Infinite end starting at midnight' => ['2020-01-01T00:00/-', '2020-01-01T00:00/-'];
        yield 'Infinite end starting at hours' => ['2020-01-01T12:00/-', '2020-01-01T00:00/-'];
        yield 'Forever' => ['-/-', '-/-'];
    }

    /**
     * @dataProvider isFullDays
     */
    public function testIsFullDays(string $input, bool $expected): void
    {
        self::assertSame($expected, LocalDateTimeInterval::parse($input)->isFullDays());
    }

    /**
     * @return iterable<mixed>
     */
    public function isFullDays(): iterable
    {
        yield ['2020-01-01T00:00/2020-01-02T00:00', true];
        yield ['2020-01-01T00:00/2020-01-04T00:00', true];

        yield ['2020-01-01T00:00/2020-01-04T01:00', false];
        yield ['2020-01-01T01:00/2020-01-04T00:00', false];
        yield ['2020-01-01T01:00/2020-01-04T01:00', false];
    }

    /**
     * @dataProvider days
     *
     * @param string[] $expected
     */
    public function testDays(string $input, array $expected): void
    {
        self::assertSame($expected, map(LocalDateTimeInterval::parse($input)->days(), static fn (LocalDate $day): string
            => (string) $day,
        ));
    }

    /**
     * @return iterable<mixed>
     */
    public function days(): iterable
    {
        yield [
            '2020-01-01T00:00/2020-01-02T00:00',
            [
                '2020-01-01',
            ],
        ];
        yield [
            '2020-01-01T12:00/2020-01-02T12:00',
            [
                '2020-01-01',
                '2020-01-02',
            ],
        ];
        yield [
            '2020-01-01T00:00/2020-01-03T01:00',
            [
                '2020-01-01',
                '2020-01-02',
                '2020-01-03',
            ],
        ];

        yield [
            '2020-01-01T00:00/2020-01-04T00:00',
            [
                '2020-01-01',
                '2020-01-02',
                '2020-01-03',
            ],
        ];
    }

    /**
     * @dataProvider getInclusiveEnd
     */
    public function testGetInclusiveEnd(string $input, string $expected): void
    {
        static::assertSame(
            $expected,
            (string) LocalDateTimeInterval::parse($input)->getInclusiveEnd(),
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function getInclusiveEnd(): iterable
    {
        yield ['-/-', ''];
        yield ['2020-01-01T00:00/2020-01-02T00:00', '2020-01-01T23:59:59.999999999'];
        yield ['2020-01-01T00:00/2020-01-01T12:34:56', '2020-01-01T12:34:55.999999999'];
    }

    /**
     * @dataProvider compareTo
     */
    public function testCompareTo(string $a, string $b, int $expected): void
    {
        self::assertSame($expected, LocalDateTimeInterval::parse($a)->compareTo(LocalDateTimeInterval::parse($b)));
        self::assertSame(-$expected, LocalDateTimeInterval::parse($b)->compareTo(LocalDateTimeInterval::parse($a)));
    }

    /**
     * @return iterable<mixed>
     */
    public function compareTo(): iterable
    {
        $ref = '2020-01-02T00:00/2020-01-03T00:00';

        /** @see https://en.wikipedia.org/wiki/Allen's_interval_algebra#Relations */
        yield 'precedes' => ['2020-01-01T00:00/2020-01-01T12:00', $ref, -1];
        yield 'isPrecededBy' => ['2020-01-04T00:00/2020-01-05T00:00', $ref, 1];
        yield 'meets' => ['2020-01-01T00:00/2020-01-02T00:00', $ref, -1];
        yield 'metBy' => ['2020-01-03T00:00/2020-01-04T00:00', $ref, 1];
        yield 'overlaps' => ['2020-01-01T00:00/2020-01-02T12:00', $ref, -1];
        yield 'overlappedBy' => ['2020-01-02T12:00/2020-01-04T00:00', $ref, 1];
        yield 'starts' => ['2020-01-02T00:00/2020-01-02T12:00', $ref, -1];
        yield 'startsBy' => ['2020-01-02T00:00/2020-01-04T00:00', $ref, 1];
        yield 'during' => ['2020-01-02T08:00/2020-01-02T16:00', $ref, 1];
        yield 'contains' => ['2020-01-01T12:00/2020-01-03T12:00', $ref, -1];
        yield 'finishes' => ['2020-01-02T12:00/2020-01-03T00:00', $ref, 1];
        yield 'finishedBy' => ['2020-01-01T12:00/2020-01-03T00:00', $ref, -1];
        yield 'equalTo' => [$ref, $ref, 0];

        // Infinite starts
        yield 'precedes infinite start' => ['-/2020-01-01T00:00', $ref, -1];
        yield 'meets infinite start' => ['-/2020-01-02T00:00', $ref, -1];
        yield 'overlaps infinite start' => ['-/2020-01-02T12:00', $ref, -1];
        yield 'finishedBy infinite start' => ['-/2020-01-03T00:00', $ref, -1];
        yield 'contains infinite start' => ['-/2020-01-04T00:00', $ref, -1];
        yield 'starts infinite start' => ['-/2020-01-02T00:00', '-/2020-01-03T00:00', -1];

        yield 'equalTo infinite start' => ['-/2020-01-03T00:00', '-/2020-01-03T00:00', 0];
        yield 'infinite start finishing after infinite start ref' => ['-/2020-01-04T00:00', '-/2020-01-03T00:00', 1];

        // Infinite ends
        yield 'contains infinite end' => ['2020-01-01T00:00/-', $ref, -1];
        yield 'startsBy infinite end' => ['2020-01-02T00:00/-', $ref, 1];
        yield 'overlappedBy infinite end' => ['2020-01-02T12:00/-', $ref, 1];
        yield 'metBy infinite end' => ['2020-01-03T00:00/-', $ref, 1];
        yield 'isPrecededBy infinite end' => ['2020-01-04T00:00/-', $ref, 1];
        yield 'finishes infinite end' => ['2020-01-03T00:00/-', '2020-01-02T00:00/-', 1];

        yield 'equalTo infinite end' => ['2020-01-03T00:00/-', '2020-01-03T00:00/-', 0];
        yield 'infinite end starting after infinite end ref' => ['2020-01-04T00:00/-', '2020-01-03T00:00/-', 1];

        // Multiple infinites
        yield 'equalTo with one infinite start + one infinite end' => ['-/2020-01-03T00:00', '2020-01-02T00:00/-', -1];

        yield 'Full infinite compared to full range' => ['-/-', '2020-01-02T00:00/2020-01-03T00:00', -1];
        yield 'Full infinite compared to infinite start' => ['-/-', '-/2020-01-03T00:00', 1];
        yield 'Full infinite compared to infinite end' => ['-/-', '2020-01-02T00:00/-', -1];
        yield 'Equal full infinite' => ['-/-', '-/-', 0];
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
            '2010|2010' => LocalDateTimeInterval::parse('2010-01-01T00:00:00/2010-01-01T00:00:00'),
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
}
