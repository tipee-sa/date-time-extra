<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Helper;

use Brick\DateTime\Duration;

final class DurationFn
{
    /**
     * @return callable(Duration|null): bool
     */
    public static function notEmpty(): callable
    {
        return static fn (?Duration $duration): bool => null !== $duration && !$duration->isZero();
    }

    /**
     * @return callable(Duration, Duration): bool
     */
    public static function equals(): callable
    {
        return static fn (Duration $a, Duration $b): bool => $a->isEqualTo($b);
    }

    /**
     * @return callable(Duration, Duration): Duration
     */
    public static function plus(): callable
    {
        return static fn (Duration $a, Duration $b): Duration => $a->plus($b);
    }
}
