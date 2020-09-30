<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Legacy;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;

final class LegacyDateConverter
{
    public static function toTimeRange(?string $start, ?string $inclusiveEnd): LocalDateTimeInterval
    {
        $startDateTime = $endDateTime = null;

        if (null !== $start) {
            $startDateTime = self::toLocalDateTime($start);
        }
        if (null !== $inclusiveEnd) {
            $endDateTime = self::toLocalDateTime($inclusiveEnd);

            // Transform to exclusive date time
            if ($endDateTime->getTime()->isEqualTo(LocalTime::min())) {
                $endDateTime = $endDateTime->plusDays(1);
            }
        }

        return LocalDateTimeInterval::between($startDateTime, $endDateTime);
    }

    private static function toLocalDateTime(string $date): LocalDateTime
    {
        return self::hasTime($date)
            ? LocalDateTime::parse(str_replace(' ', 'T', $date))
            : LocalDate::parse($date)->atTime(LocalTime::min());
    }

    private static function hasTime(string $date): bool
    {
        return 1 === preg_match('# \d{2}:\d{2}(?::\d{2})?$#', $date);
    }
}
