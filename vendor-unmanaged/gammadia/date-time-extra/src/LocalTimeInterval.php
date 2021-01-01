<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Throwable;
use Webmozart\Assert\Assert;
use function Gammadia\Collections\Functional\map;

final class LocalTimeInterval
{
    /**
     * @var string
     */
    private const SEPARATOR = '/';

    /**
     * @var string
     */
    private const INFINITY = '-';

    /**
     * @var int
     */
    private const FINITE = 0;

    /**
     * @var int
     */
    private const INFINITE_START = 1;

    /**
     * @var int
     */
    private const INFINITE_END = 2;

    /**
     * @var LocalTime
     */
    private $timepoint;

    /**
     * @var Duration|null
     */
    private $duration;

    /**
     * @var int
     */
    private $finitude;

    /**
     * @var LocalDate
     */
    private static $arbitraryDateCache;

    private function __construct(LocalTime $timepoint, ?Duration $duration = null, int $finitude = self::FINITE)
    {
        $this->timepoint = $timepoint;
        $this->duration = $duration;
        $this->finitude = $finitude;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public static function for(LocalTime $timepoint, Duration $duration): self
    {
        return new self($timepoint, $duration);
    }

    public static function from(LocalDateTimeInterval $timeRange): self
    {
        Assert::false($timeRange->hasInfiniteStart() && $timeRange->hasInfiniteEnd(), 'A timepoint is mandatory.');

        $timepoint = (!$timeRange->hasInfiniteStart() ? $timeRange->getFiniteStart() : $timeRange->getFiniteEnd())
            ->getTime();
        $duration = $timeRange->isFinite() ? $timeRange->getDuration() : null;
        $finitude = $timeRange->isFinite()
            ? self::FINITE
            : ($timeRange->hasInfiniteStart() ? self::INFINITE_START : self::INFINITE_END);

        return new self($timepoint, $duration, $finitude);
    }

    public static function between(?LocalTime $startTime, ?LocalTime $endTime): self
    {
        Assert::false(null === $startTime && null === $endTime, 'A timepoint is mandatory.');

        // For finite range, we calculate the duration between the two hour ranges (including overnight scenarios)
        if (null !== $startTime && null !== $endTime) {
            $start = self::arbitraryDate()->atTime($startTime);
            $end = self::arbitraryDate()->atTime($endTime);
            if ($end->isBefore($start)) {
                $end = $end->plusDays(1);
            }

            $timepoint = $startTime;
            $duration = LocalDateTimeInterval::between($start, $end)->getDuration();
            $finitude = self::FINITE;
        } else {
            $timepoint = $endTime ?? $startTime;
            Assert::notNull($timepoint);
            $duration = null;
            $finitude = null === $endTime ? self::INFINITE_END : self::INFINITE_START;
        }

        return new self($timepoint, $duration, $finitude);
    }

    public static function empty(?LocalTime $timepoint = null): self
    {
        return new self($timepoint ?? LocalTime::min(), Duration::zero());
    }

    public static function until(LocalTime $timepoint): self
    {
        return new self($timepoint, null, self::INFINITE_START);
    }

    public static function since(LocalTime $timepoint): self
    {
        return new self($timepoint, null, self::INFINITE_END);
    }

    public static function ofDays(int $days): self
    {
        return new self(LocalTime::min(), Duration::ofDays($days));
    }

    public static function containerOf(self ...$localTimeIntervals): self
    {
        $container = LocalDateTimeInterval::containerOf(
            ...map(
                $localTimeIntervals,
                static function (self $localTimeInterval): LocalDateTimeInterval {
                    return $localTimeInterval->atDate(self::arbitraryDate());
                }
            )
        );

        return null !== $container ? self::from($container) : self::empty();
    }

    public function isEqualTo(self $other): bool
    {
        return $this->timepoint->isEqualTo($other->timepoint)
            && ((null === $this->duration && null === $other->duration) || (
                (null !== $this->duration && null !== $other->duration) && $this->duration->isEqualTo($other->duration)
            ))
            && $this->finitude === $other->finitude;
    }

    public function toString(): string
    {
        if ($this->hasInfiniteStart()) {
            $arguments = [self::INFINITY, $this->timepoint];
        } elseif ($this->hasInfiniteEnd()) {
            $arguments = [$this->timepoint, self::INFINITY];
        } else {
            $arguments = [$this->timepoint, $this->duration];
        }

        return implode(self::SEPARATOR, $arguments);
    }

    /**
     * @param string $textToParse A LocalTime + optional Duration (12:34/PT2H, PT2H/12:34, -/12:34 or 12:34/-)
     */
    public static function parse(string $textToParse): self
    {
        try {
            Assert::contains($textToParse, self::SEPARATOR);
            [$firstPart, $secondPart] = explode(self::SEPARATOR, $textToParse);
            Assert::false(self::INFINITY === $firstPart && self::INFINITY === $secondPart, 'A timepoint is mandatory.');

            if (self::INFINITY === $firstPart) {
                $timepoint = LocalTime::parse($secondPart);
                $duration = null;
                $finitude = self::INFINITE_START;
            } elseif (self::INFINITY === $secondPart) {
                $timepoint = LocalTime::parse($firstPart);
                $duration = null;
                $finitude = self::INFINITE_END;
            } else {
                // Let's allow for reversed arguments, because we can and it doesn't matter
                try {
                    $timepoint = LocalTime::parse($firstPart);
                    $duration = Duration::parse($secondPart);
                } catch (\Throwable $throwable) {
                    $timepoint = LocalTime::parse($secondPart);
                    $duration = Duration::parse($firstPart);
                }
                $finitude = self::FINITE;
            }

            return new self($timepoint, $duration, $finitude);
        } catch (Throwable $throwable) {
            throw IntervalParseException::localTimeInterval($textToParse, $throwable);
        }
    }

    public function atDate(LocalDate $date): LocalDateTimeInterval
    {
        $localDateTime = $this->timepoint->atDate($date);
        $start = $end = null;

        if (!$this->hasInfiniteStart()) {
            $start = null !== $this->duration && $this->duration->isNegative()
                ? $localDateTime->plusDuration($this->duration)
                : $localDateTime;
        }
        if (!$this->hasInfiniteEnd()) {
            $end = null !== $this->duration && $this->duration->isPositive()
                ? $localDateTime->plusDuration($this->duration)
                : $localDateTime;
        }

        return LocalDateTimeInterval::between($start, $end);
    }

    public function isEmpty(): bool
    {
        return null !== $this->duration && $this->duration->isZero();
    }

    public function isFinite(): bool
    {
        return self::FINITE === $this->finitude;
    }

    public function hasInfiniteStart(): bool
    {
        return self::INFINITE_START === $this->finitude;
    }

    public function hasInfiniteEnd(): bool
    {
        return self::INFINITE_END === $this->finitude;
    }

    private static function arbitraryDate(): LocalDate
    {
        if (null === self::$arbitraryDateCache) {
            self::$arbitraryDateCache = LocalDate::parse('2020-01-02');
        }

        return self::$arbitraryDateCache;
    }
}
