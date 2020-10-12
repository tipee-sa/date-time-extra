<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Helper;

use App\Domain\Shared\Percentage;
use Brick\DateTime\Duration;
use Gammadia\DateTimeExtra\Helper\DurationHelper;
use PHPUnit\Framework\TestCase;

final class DurationHelperTest extends TestCase
{
    /**
     * @dataProvider hoursToDuration
     */
    public function testHoursToDuration(float $hours, Duration $expected): void
    {
        self::assertSame((string) $expected, (string) DurationHelper::hoursToDuration($hours));
    }

    /**
     * @return iterable<mixed>
     */
    public function hoursToDuration(): iterable
    {
        yield [48, Duration::ofDays(2)];
        yield [8, Duration::ofHours(8)];
        yield [8.25, Duration::ofMinutes(495)];
        yield [0.01, Duration::ofSeconds(36)];
        yield [0.000005, Duration::ofMillis(18)];
        yield [0.00000000001, Duration::ofNanos(36)];
    }

    /**
     * @dataProvider applyPercentage
     */
    public function testApplyPercentage(Duration $input, Percentage $rate, Duration $expected): void
    {
        self::assertSame((string) $expected, (string) DurationHelper::applyPercentage($input, $rate));
    }

    /**
     * @return iterable<mixed>
     */
    public function applyPercentage(): iterable
    {
        yield [Duration::ofDays(365), Percentage::total(), Duration::ofDays(365)];
        yield [Duration::ofHours(8), Percentage::of(50.0), Duration::ofHours(4)];
        yield [Duration::ofHours(8), Percentage::ofRatio(1, 2), Duration::ofHours(4)];
        yield [Duration::ofMinutes(10), Percentage::of(12.34), Duration::ofMillis(74040)];
        yield [Duration::ofSeconds(1), Percentage::of(0.08), Duration::ofNanos(800000)];
        yield [Duration::ofMillis(123), Percentage::of(0.1), Duration::ofNanos(123000)];
        yield [Duration::ofNanos(90), Percentage::of(50.0), Duration::ofNanos(45)];
        yield [Duration::ofNanos(90), Percentage::zero(), Duration::ofNanos(0)];
    }
}
