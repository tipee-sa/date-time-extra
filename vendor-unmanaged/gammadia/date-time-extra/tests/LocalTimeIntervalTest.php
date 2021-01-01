<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\IntervalParseException;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use Gammadia\DateTimeExtra\LocalTimeInterval;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class LocalTimeIntervalTest extends TestCase
{
    /**
     * @var string
     */
    private const DATE = '2020-01-02';

    /**
     * @return iterable<mixed>
     */
    public function emptyISO(): iterable
    {
        $empty = '2020-01-02T00:00/2020-01-02T00:00';
        yield ['00:00/PT-0M', $empty];
        yield ['00:00/PT0M', $empty];
        yield ['00:00:00/PT0S', $empty];

        $empty = '2020-01-02T12:00/2020-01-02T12:00';
        yield ['12:00/PT-0M', $empty];
        yield ['12:00/PT0M', $empty];
        yield ['12:00:00/PT0S', $empty];

        $empty = '2020-01-02T23:59/2020-01-02T23:59';
        yield ['23:59/PT-0M', $empty];
        yield ['23:59/PT0M', $empty];
        yield ['23:59:59/PT0S', '2020-01-02T23:59:59/2020-01-02T23:59:59'];

        yield 'Reversed arguments work too' => ['PT0S/08:00'];
    }

    /**
     * @return iterable<mixed>
     */
    public function validISO(): iterable
    {
        // Positive intervals at different times
        yield ['00:00/PT12H', '2020-01-02T00:00/2020-01-02T12:00'];
        yield ['00:00/PT24H', '2020-01-02T00:00/2020-01-03T00:00'];
        yield ['00:00/PT48H', '2020-01-02T00:00/2020-01-04T00:00'];
        yield ['00:00/PT8H24M37S', '2020-01-02T00:00/2020-01-02T08:24:37'];
        yield ['12:00/PT12H', '2020-01-02T12:00/2020-01-03T00:00'];
        yield ['12:00/PT24H', '2020-01-02T12:00/2020-01-03T12:00'];
        yield ['12:00/PT48H', '2020-01-02T12:00/2020-01-04T12:00'];
        yield ['12:00/PT8H24M37S', '2020-01-02T12:00/2020-01-02T20:24:37'];
        yield ['23:59/PT12H', '2020-01-02T23:59/2020-01-03T11:59'];
        yield ['23:59/PT24H', '2020-01-02T23:59/2020-01-03T23:59'];
        yield ['23:59/PT48H', '2020-01-02T23:59/2020-01-04T23:59'];
        yield ['23:59/PT8H24M37S', '2020-01-02T23:59/2020-01-03T08:23:37'];

        // Negative intervals
        yield ['00:00/PT-12H', '2020-01-01T12:00/2020-01-02T00:00'];
        yield ['00:00/PT-24H', '2020-01-01T00:00/2020-01-02T00:00'];
        yield ['00:00/PT-48H', '2019-12-31T00:00/2020-01-02T00:00'];
        yield ['00:00/PT-8H-24M-37S', '2020-01-01T15:35:23/2020-01-02T00:00'];
        yield ['12:00/PT-12H', '2020-01-02T00:00/2020-01-02T12:00'];
        yield ['12:00/PT-24H', '2020-01-01T12:00/2020-01-02T12:00'];
        yield ['12:00/PT-48H', '2019-12-31T12:00/2020-01-02T12:00'];
        yield ['12:00/PT-8H-24M-37S', '2020-01-02T03:35:23/2020-01-02T12:00'];
        yield ['23:59/PT-12H', '2020-01-02T11:59/2020-01-02T23:59'];
        yield ['23:59/PT-24H', '2020-01-01T23:59/2020-01-02T23:59'];
        yield ['23:59/PT-48H', '2019-12-31T23:59/2020-01-02T23:59'];
        yield ['23:59/PT-8H-24M-37S', '2020-01-02T15:34:23/2020-01-02T23:59'];

        // Mixed intervals (negative and positives)
        yield 'Negative overall duration with positive internals' => [
            '00:00/PT-8H24M-37S',
            '2020-01-01T16:23:23/2020-01-02T00:00',
        ];
        yield 'Positive overall duration with negative internals' => [
            '00:00/PT8H-24M37S',
            '2020-01-02T00:00/2020-01-02T07:36:37',
        ];

        // Infinite start or end
        yield ['-/00:00', '-/2020-01-02T00:00'];
        yield ['00:00/-', '2020-01-02T00:00/-'];

        // Misc
        yield 'Reversed arguments work too' => ['PT2H/08:00', '2020-01-02T08:00/2020-01-02T10:00'];
        yield '3 year (365 * 3) interval ends up one day sooner because 2020 is a leap year with 366 days' => [
            '00:00/P1095D',
            '2020-01-02T00:00/2023-01-01T00:00',
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public function isEqualTo(): iterable
    {
        yield ['12:00/PT2H', '12:00/PT2H', true];
        yield ['12:00/PT2H', '12:00/PT120M', true];
        yield ['12:00/-', '12:00/-', true];
        yield ['-/12:00', '-/12:00', true];

        yield ['12:00/PT2H', '10:00/PT2H', false];
        yield ['12:00/PT2H', '12:00/PT1H', false];
        yield ['12:00/-', '-/12:00', false];
        yield ['12:00/-', '12:00/PT1H', false];
        yield ['-/12:00', '12:00/PT1H', false];
    }

    /**
     * @return iterable<mixed>
     */
    public function invalidISO(): iterable
    {
        yield 'An infinite time interval makes no sense, there must be a timepoint.' => ['-/-'];
        yield '24:00 format is not supported' => ['24:00-PT0S'];
        yield 'Two LocalTimes are not supported, use ::between() instead.' => ['08:00/12:00'];
        yield 'Bad separator' => ['08:00-PT12H'];
    }

    /**
     * @return iterable<mixed>
     */
    public function validTimeRanges(): iterable
    {
        $date = LocalDate::parse(self::DATE);

        yield [LocalDateTimeInterval::day($date), '00:00/PT24H'];
        yield [LocalDateTimeInterval::since($date->atTime(LocalTime::min())), '00:00/-'];
        yield [LocalDateTimeInterval::until($date->atTime(LocalTime::min())), '-/00:00'];
    }

    /**
     * @return iterable<mixed>
     */
    public function invalidTimeRanges(): iterable
    {
        yield [LocalDateTimeInterval::forever()];
    }

    /**
     * @dataProvider emptyISO
     * @dataProvider validISO
     */
    public function testParse(string $iso): void
    {
        LocalTimeInterval::parse($iso);
        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider invalidISO
     */
    public function testParseInvalid(string $iso): void
    {
        $this->expectException(IntervalParseException::class);
        LocalTimeInterval::parse($iso);
    }

    /**
     * @dataProvider validISO
     */
    public function testAtDate(string $iso, string $expected): void
    {
        self::assertSame($expected, (string) LocalTimeInterval::parse($iso)->atDate(LocalDate::parse(self::DATE)));
    }

    /**
     * @dataProvider emptyISO
     */
    public function testIsEmpty(string $iso): void
    {
        self::assertTrue(LocalTimeInterval::parse($iso)->isEmpty());
    }

    /**
     * @dataProvider validISO
     */
    public function testIsNotEmpty(string $iso): void
    {
        self::assertFalse(LocalTimeInterval::parse($iso)->isEmpty());
    }

    /**
     * @dataProvider validTimeRanges
     */
    public function testFromNamedConstructor(LocalDateTimeInterval $timeRange, string $expected): void
    {
        $localTimeInterval = LocalTimeInterval::from($timeRange);
        self::assertSame($expected, (string) $localTimeInterval);

        // Reconstructing the timerange works if you give the exact same date
        self::assertSame((string) $timeRange, (string) $localTimeInterval->atDate(LocalDate::parse(self::DATE)));
        self::assertNotSame((string) $timeRange, (string) $localTimeInterval->atDate(LocalDate::parse('2020-01-01')));
    }

    /**
     * @dataProvider invalidTimeRanges
     */
    public function testFromNamedConstructorDoesntSupportForeverRanges(LocalDateTimeInterval $timeRange): void
    {
        $this->expectException(InvalidArgumentException::class);

        LocalTimeInterval::from($timeRange);
    }

    /**
     * @dataProvider isEqualTo
     */
    public function testIsEqualTo(string $a, string $b, bool $expected): void
    {
        self::assertSame($expected, LocalTimeInterval::parse($a)->isEqualTo(LocalTimeInterval::parse($b)));
    }

    public function testEmptyNamedConstructor(): void
    {
        self::assertSame(
            (string) LocalTimeInterval::empty(LocalTime::parse('12:34')),
            (string) LocalTimeInterval::for(LocalTime::parse('12:34'), Duration::zero())
        );
    }

    public function testForNamedConstructor(): void
    {
        self::assertSame(
            (string) LocalTimeInterval::parse('12:34/PT2H'),
            (string) LocalTimeInterval::for(LocalTime::parse('12:34'), Duration::ofHours(2))
        );
    }

    public function testOfDaysNamedConstructor(): void
    {
        self::assertSame((string) LocalTimeInterval::parse('00:00/P1D'), (string) LocalTimeInterval::ofDays(1));
        self::assertSame((string) LocalTimeInterval::parse('00:00/P30D'), (string) LocalTimeInterval::ofDays(30));
    }

    public function testContainerOf(): void
    {
        self::assertSame((string) LocalTimeInterval::empty(), (string) LocalTimeInterval::containerOf());

        $intervals = [
            LocalTimeInterval::parse('12:00/PT-2H'),
            LocalTimeInterval::parse('15:00/PT30M'),
            LocalTimeInterval::parse('22:00/PT16H'),
        ];

        self::assertSame('10:00/PT28H', (string) LocalTimeInterval::containerOf(...$intervals));

        $infiniteStartIntervals = [
            LocalTimeInterval::parse('12:00/PT2H'),
            LocalTimeInterval::parse('-/15:00'),
        ];
        self::assertSame('-/15:00', (string) LocalTimeInterval::containerOf(...$infiniteStartIntervals));

        $infiniteEndIntervals = [
            LocalTimeInterval::parse('12:00/PT2H'),
            LocalTimeInterval::parse('15:00/-'),
        ];
        self::assertSame('12:00/-', (string) LocalTimeInterval::containerOf(...$infiniteEndIntervals));
    }

    public function testToString(): void
    {
        $localTimeInterval = LocalTimeInterval::for(LocalTime::parse('12:34'), Duration::ofHours(2)->plusMinutes(30));

        self::assertSame('12:00/-', (string) LocalTimeInterval::since(LocalTime::parse('12:00')));
        self::assertSame('-/12:00', (string) LocalTimeInterval::until(LocalTime::parse('12:00')));

        self::assertSame('12:34/PT2H30M', (string) $localTimeInterval);
        self::assertSame((string) $localTimeInterval, $localTimeInterval->toString());
    }

    /**
     * @dataProvider between
     */
    public function testBetween(string $expectedIso, ?string $startTimeIso, ?string $endTimeIso): void
    {
        $startTime = null !== $startTimeIso ? LocalTime::parse($startTimeIso) : null;
        $endTime = null !== $endTimeIso ? LocalTime::parse($endTimeIso) : null;

        self::assertSame($expectedIso, (string) LocalTimeInterval::between($startTime, $endTime));
    }

    /**
     * @return iterable<mixed>
     */
    public function between(): iterable
    {
        yield 'Two midnights equals an empty duration starting at midnight.' => ['00:00/PT0S', '00:00', '00:00'];

        yield ['00:00/PT12H', '00:00', '12:00'];
        yield ['12:00/PT12H', '12:00', '00:00'];
        yield ['00:00/PT1M', '00:00', '00:01'];
        yield ['00:00/PT23H59M', '00:00', '23:59'];
        yield ['18:00/PT14H', '18:00', '08:00'];

        yield ['18:00/-', '18:00', null];
        yield ['-/18:00', null, '18:00'];
    }
}
