<?php

namespace Gammadia\DateTimeExtra\ZonedDateTime;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\ZonedDateTime;

class Boundary
{
    public static function infiniteFuture(): ZonedDateTime
    {
        return ZonedDateTime::of(9999, 12, 23, 23, 59, 59, 59);
    }

    public static function isInfiniteFuture(ZonedDateTime $dateTime): bool
    {
        return $dateTime->isEqualTo(self::infiniteFuture());
    }

    public static function isInfinitePaste(ZonedDateTime $dateTime): bool
    {
        return $dateTime->isEqualTo(self::infinitePaste());
    }

    public static function infinitePaste(): ZonedDateTime
    {
        return ZonedDateTime::of(0, 1, 1);
    }
}
