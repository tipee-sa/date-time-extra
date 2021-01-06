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

    /*
     * Re-usable data providers
     */

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

        // Mixed intervals (negative and positives)
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

        // Negative intervals
        yield ['00:00/PT-12H', '2020-01-01T12:00/2020-01-02T00:00'];
        yield ['12:00/PT-48H', '2019-12-31T00:00/2020-01-02T00:00'];
        yield ['23:59:59/PT-8H-24M-37S', '2020-01-01T15:35:23/2020-01-02T00:00'];
        yield 'Negative overall duration with positive internals' => ['00:00/PT-8H24M-37S'];
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

    /*
     * Named constructors tests
     */

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

    public function testEmptyNamedConstructor(): void
    {
        self::assertSame(
            (string) LocalTimeInterval::empty(LocalTime::parse('12:34')),
            (string) LocalTimeInterval::finite(LocalTime::parse('12:34'), Duration::zero())
        );
    }

    public function testFiniteNamedConstructor(): void
    {
        self::assertSame(
            (string) LocalTimeInterval::parse('12:34/PT2H'),
            (string) LocalTimeInterval::finite(LocalTime::parse('12:34'), Duration::ofHours(2))
        );
    }

    public function testFiniteNamedConstructorWithNegativeDurationIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);

        LocalTimeInterval::finite(LocalTime::min(), Duration::ofHours(-2));
    }

    public function testOfDaysNamedConstructor(): void
    {
        self::assertSame((string) LocalTimeInterval::parse('00:00/P1D'), (string) LocalTimeInterval::ofDays(1));
        self::assertSame((string) LocalTimeInterval::parse('00:00/P30D'), (string) LocalTimeInterval::ofDays(30));
    }

    public function testOfDaysNamedConstructorWithNegativeDurationIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);

        LocalTimeInterval::ofDays(-2);
    }

    /*
     * Converters tests
     */

    /**
     * @dataProvider validISO
     */
    public function testAtDate(string $iso, string $expected): void
    {
        self::assertSame($expected, (string) LocalTimeInterval::parse($iso)->atDate(LocalDate::parse(self::DATE)));
    }

    public function testToString(): void
    {
        $localTimeInterval = LocalTimeInterval::finite(LocalTime::parse('12:34'), Duration::ofHours(2)->plusMinutes(30));

        self::assertSame('12:00/-', (string) LocalTimeInterval::since(LocalTime::parse('12:00')));
        self::assertSame('-/12:00', (string) LocalTimeInterval::until(LocalTime::parse('12:00')));

        self::assertSame('12:34/PT2H30M', (string) $localTimeInterval);
        self::assertSame((string) $localTimeInterval, $localTimeInterval->toString());
    }

    /*
     * Transformers tests
     */

    /**
     * @dataProvider withTimepoint
     */
    public function testWithTimepoint(string $iso, string $timepointIso, string $expected): void
    {
        self::assertSame(
            $expected,
            (string) LocalTimeInterval::parse($iso)->withTimepoint(LocalTime::parse($timepointIso))
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function withTimepoint(): iterable
    {
        yield 'No change' => ['00:00/PT12H', '00:00', '00:00/PT12H'];
        yield 'Forward' => ['00:00/PT12H', '12:00', '12:00/PT12H'];
        yield 'Backward' => ['14:00/PT2H', '01:00', '01:00/PT2H'];
        yield 'With minutes' => ['14:00/PT1M', '23:59', '23:59/PT1M'];
        yield 'With seconds' => ['14:00/PT1M', '23:59:59', '23:59:59/PT1M'];
        yield 'Infinite start' => ['-/14:00', '12:00', '-/12:00'];
        yield 'Infinite end' => ['14:00/-', '12:00', '12:00/-'];
    }

    /**
     * @dataProvider withDuration
     */
    public function testWithDuration(string $iso, ?string $durationIso, string $expected): void
    {
        self::assertSame(
            $expected,
            (string) LocalTimeInterval::parse($iso)->withDuration(
                null !== $durationIso ? Duration::parse($durationIso) : null
            )
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function withDuration(): iterable
    {
        yield 'No change' => ['00:00/PT12H', 'PT2H', '00:00/PT2H'];
        yield 'No change infinite start' => ['-/00:00', null, '-/00:00'];
        yield 'No change infinite end' => ['00:00/-', null, '00:00/-'];

        yield 'Increase' => ['00:00/PT2H', 'PT12H', '00:00/PT12H'];
        yield 'Decrease' => ['12:00/PT2H', 'PT1H', '12:00/PT1H'];
        yield 'With minutes' => ['12:00/PT1H', 'PT30M', '12:00/PT30M'];
        yield 'With seconds' => ['12:00/PT1H', 'PT15M30S', '12:00/PT15M30S'];

        yield 'Infinite start to finite' => ['-/12:00', 'PT1H', '12:00/PT1H'];
        yield 'Infinite end to finite' => ['12:00/-', 'PT1H', '12:00/PT1H'];

        yield 'Finite to infinite end (finite to infinite start is not possible through this method' => [
            '12:00/PT1H',
            null,
            '12:00/-',
        ];
    }

    /**
     * @dataProvider move
     */
    public function testMove(string $iso, Duration $duration, string $expected): void
    {
        self::assertSame($expected, (string) LocalTimeInterval::parse($iso)->move($duration));
    }

    /**
     * @return iterable<mixed>
     */
    public function move(): iterable
    {
        yield 'No change' => ['00:00/PT1H', Duration::zero(), '00:00/PT1H'];
        yield 'Forward' => ['00:00/PT0S', Duration::ofMinutes(30), '00:30/PT0S'];
        yield 'Backward' => ['00:00/PT0S', Duration::ofMinutes(-30), '23:30/PT0S'];
    }

    /**
     * @dataProvider collapse
     */
    public function testCollapse(string $iso, string $expected): void
    {
        self::assertSame($expected, (string) LocalTimeInterval::parse($iso)->collapse());
    }

    /**
     * @return iterable<mixed>
     */
    public function collapse(): iterable
    {
        yield 'Finite' => ['00:00/PT2H', '00:00/PT0S'];
        yield 'With seconds' => ['12:34:56/PT4H30M12S', '12:34:56/PT0S'];
        yield 'Infinite start' => ['-/00:00', '00:00/PT0S'];
        yield 'Infinite end' => ['00:00/-', '00:00/PT0S'];
    }

    /**
     * @dataProvider toFullDays
     */
    public function testToFullDays(string $input, string $expected): void
    {
        self::assertSame(
            (string) LocalTimeInterval::parse($expected),
            (string) LocalTimeInterval::parse($input)->toFullDays()
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function toFullDays(): iterable
    {
        yield ['00:00/PT0S', '00:00/PT0S'];
        yield ['00:00/PT24H', '00:00/PT24H'];
        yield ['00:00/PT4H', '00:00/PT24H'];
        yield ['12:00/PT24H', '00:00/PT48H'];

        // Infinite values are a bit weird to comprehend without the notion of a date, but that's okay
        yield ['12:00/-', '00:00/-'];
        yield ['-/12:00', '-/00:00'];
    }

    /*
     * Testers tests
     */

    /**
     * @dataProvider isFullDays
     */
    public function testIsFullDays(string $iso, bool $expected): void
    {
        self::assertSame($expected, LocalTimeInterval::parse($iso)->isFullDays());
    }

    /**
     * @return iterable<mixed>
     */
    public function isFullDays(): iterable
    {
        yield ['00:00/PT0S', true];
        yield ['00:00/PT24H', true];
        yield ['00:00/PT48H', true];
        yield ['00:00/P23D', true];

        yield ['12:00/PT12H', false];
        yield ['00:00/PT12H', false];
        yield ['00:00/PT24H20M', false];
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

    /*
     * Comparators tests
     */

    /**
     * @dataProvider isEqualTo
     */
    public function testIsEqualTo(string $a, string $b, bool $expected): void
    {
        self::assertSame($expected, LocalTimeInterval::parse($a)->isEqualTo(LocalTimeInterval::parse($b)));
    }
}
