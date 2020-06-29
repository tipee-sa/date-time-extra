<?php

namespace Gammadia\DateTimeExtra\Instant;

use Brick\DateTime\Instant;

class Boundary
{
    public static function infiniteFuture(): Instant
    {
        return Instant::of(9999, 12, 23, 23, 59, 59, 59);
    }

    public static function isInfiniteFuture(Instant $dateTime): bool
    {
        return $dateTime->isEqualTo(self::infiniteFuture());
    }

    public static function isInfinitePaste(Instant $dateTime): bool
    {
        return $dateTime->isEqualTo(self::infinitePaste());
    }

    public static function infinitePaste(): Instant
    {
        return Instant::of(0, 1, 1);
    }
}
