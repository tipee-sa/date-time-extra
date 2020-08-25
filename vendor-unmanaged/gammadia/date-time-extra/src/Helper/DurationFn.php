<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Helper;

use Brick\DateTime\Duration;

final class DurationFn
{
    public static function notEmpty(): callable
    {
        return static function (?Duration $duration): bool {
            return null !== $duration && !$duration->isZero();
        };
    }

    public static function equals(): callable
    {
        return static function (Duration $a, Duration $b): bool {
            return $a->isEqualTo($b);
        };
    }

    public static function plus(): callable
    {
        return static function (Duration $a, Duration $b): Duration {
            return $a->plus($b);
        };
    }
}
