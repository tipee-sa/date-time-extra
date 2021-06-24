<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Helper;

use Brick\DateTime\Duration;

final class DurationFn
{
    public static function notEmpty(): callable
    {
        return static fn (?Duration $duration): bool => null !== $duration && !$duration->isZero();
    }

    public static function equals(): callable
    {
        return static fn (Duration $a, Duration $b): bool => $a->isEqualTo($b);
    }

    public static function plus(): callable
    {
        return static fn (Duration $a, Duration $b): Duration => $a->plus($b);
    }
}
