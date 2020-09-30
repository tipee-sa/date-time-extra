<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Legacy;

use Gammadia\DateTimeExtra\Legacy\LegacyDateConverter;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use PHPUnit\Framework\TestCase;

final class LegacyDateConverterTest extends TestCase
{
    /**
     * @dataProvider legacyTimeRanges
     */
    public function testToTimeRange(?string $start, ?string $end, string $expected): void
    {
        self::assertSame(
            (string) LocalDateTimeInterval::parse($expected),
            (string) LegacyDateConverter::toTimeRange($start, $end)
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function legacyTimeRanges(): iterable
    {
        yield [null, null, '-/-'];

        // End date gets transformed to exclusive date ..
        yield ['2020-01-01', '2020-01-01', '2020-01-01T00:00/2020-01-02T00:00'];
        yield ['2020-01-01', '2020-01-01 00:00', '2020-01-01T00:00/2020-01-02T00:00'];
        yield ['2020-01-01', '2020-01-01 00:00:00', '2020-01-01T00:00/2020-01-02T00:00'];

        // .. except if there's a specific hour
        yield ['2020-01-01', '2020-01-01 12:34', '2020-01-01T00:00/2020-01-01T12:34:00'];
        yield ['2020-01-01 12:34', '2020-01-01 12:34', '2020-01-01T12:34/2020-01-01T12:34:00'];
        yield ['2020-01-01 12:34:56', '2020-01-01 12:34:56', '2020-01-01T12:34:56/2020-01-01T12:34:56'];
    }
}
