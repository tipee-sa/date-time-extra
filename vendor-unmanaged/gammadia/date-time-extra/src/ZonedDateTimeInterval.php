<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\ZonedDateTime;

//todo voir InstantInterval.php
class ZonedDateTimeInterval
{
    /**
     * @var ZonedDateTime|null
     */
    private $start;

    /**
     * @var ZonedDateTime|null
     */
    private $end;

    private function __construct(?ZonedDateTime $start, ?ZonedDateTime $end)
    {
        if ($start && $end && $start->isAfter($end)) {
            throw new \InvalidArgumentException("Start after end: ${start} / ${end}");
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Creates a finite half-open interval between given time points.
     *
     * @param  ZonedDateTime|null $start   the zoned datetime of lower boundary (inclusive)
     * @param  ZonedDateTime|null $end     the zoned datetime of upper boundary (exclusive)
     *
     * @return  self ZonedDateTimeInterval interval
     */
    public static function between(?ZonedDateTime $start, ?ZonedDateTime $end): self
    {
        return new self($start, $end);
    }

    public function atUTC(): InstantInterval
    {
        return InstantInterval::between(
            $this->start ? $this->start->getInstant() : null,
            $this->end ? $this->end->getInstant() : null
        );
    }

    /**
     * <p>Yields the length of this interval in given units and applies
     * a timezone offset correction . </p>
     *
     * @return  duration in given units including a zonal correction
     */
    public function getDuration(): Duration
    {
        if (!$this->isFinite()) {
            throw new \RuntimeException('Yield duration with infinite boundary is not possible.');
        }

        return Duration::between(
            $this->getFiniteStart()->getInstant(),
            $this->getFiniteEnd()->getInstant()
        );
    }

    /**
     * <p>Yields the start time point if not null. </p>
     */
    public function getFiniteStart(): ZonedDateTime
    {
        if (null === $this->start) {
            throw new \RuntimeException('getFiniteStart() method can not return null');
        }

        return $this->start;
    }

    /**
     * <p>Yields the start time point if not null. </p>
     */
    public function getFiniteEnd(): ZonedDateTime
    {
        if (null === $this->end) {
            throw new \RuntimeException('getFiniteEnd() method can not return null');
        }

        return $this->end;
    }

    /**
     * <p>Compares the boundaries (start and end) and also the time axis
     * of this and the other interval. </p>
     */
    public function equals(self $other): bool
    {
        if ($this->hasInfiniteStart() !== $other->hasInfiniteStart() ||
            $this->hasInfiniteEnd() !== $other->hasInfiniteEnd()) {
            return false;
        }

        return
            ($this->hasInfiniteStart() || $this->getFiniteStart()->isEqualTo($other->getFiniteStart())) &&
            ($this->hasInfiniteEnd() || $this->getFiniteEnd()->isEqualTo($other->getFiniteEnd()))
            ;
    }

    /**
     * <p>Determines if this interval has finite boundaries. </p>
     */
    public function isFinite(): bool
    {
        return !($this->hasInfiniteStart() || $this->hasInfiniteEnd());
    }

    /**
     * <p>Determines if this interval has infinite start boundary. </p>
     */
    public function hasInfiniteStart(): bool
    {
        return null === $this->start;
    }

    /**
     * <p>Determines if this interval has infinite end boundary. </p>
     */
    public function hasInfiniteEnd(): bool
    {
        return null === $this->end;
    }
}
