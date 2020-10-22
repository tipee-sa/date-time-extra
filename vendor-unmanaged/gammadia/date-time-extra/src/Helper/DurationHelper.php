<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Helper;

use App\Domain\Shared\Percentage;
use Brick\DateTime\Duration;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;

final class DurationHelper
{
    public static function percentage(Duration $a, Duration $b): Percentage
    {
        return Percentage::ofRatio($a->toMillis(), $b->toMillis());
    }

    public static function hoursToDuration(float $hours): Duration
    {
        return Duration::ofMillis((int) round(
            $hours * LocalTime::SECONDS_PER_HOUR * LocalTime::MILLIS_PER_SECOND,
            PHP_ROUND_HALF_EVEN
        ));
    }

    public static function applyPercentage(Duration $duration, Percentage $rate): Duration
    {
        return Duration::ofMillis((int) round($duration->toMillis() * $rate->factor(), PHP_ROUND_HALF_EVEN));
    }

    public static function applyDailyPercentage(Duration $duration, LocalDateTimeInterval $timeRange): Duration
    {
        $timeRangeDuration = $timeRange->getDuration();
        $oneDayDuration = Duration::ofDays(1);

        return self::applyPercentage(
            $duration,
            $timeRangeDuration->isGreaterThan($oneDayDuration)
                ? self::percentage($oneDayDuration, $timeRangeDuration)
                : self::percentage($timeRangeDuration, $oneDayDuration)
        );
    }
}
