<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * @todo Complete this
 */
class InstantInterval
{
    /**
     * @var Instant|null
     */
    private $start;

    /**
     * @var Instant|null
     */
    private $end;

    private function __construct(?Instant $start, ?Instant $end)
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
     * @param  Instant|null $start   the zoned datetime of lower boundary (inclusive)
     * @param  Instant|null $end     the zoned datetime of upper boundary (exclusive)
     *
     * @return  self InstantInterval interval
     */
    public static function between(?Instant $start, ?Instant $end): self
    {
        return new self($start, $end);
    }

    /**
     * Yields the start time point if not null.
     */
    public function getFiniteStart(): Instant
    {
        if (null === $this->start) {
            throw new \RuntimeException('getFiniteStart() method can not return null');
        }

        return $this->start;
    }

    /**
     * Yields the start time point if not null.
     */
    public function getFiniteEnd(): Instant
    {
        if (null === $this->end) {
            throw new \RuntimeException('getFiniteEnd() method can not return null');
        }

        return $this->end;
    }

    public function getDuration(): Duration
    {
        if (!$this->isFinite()) {
            throw new \RuntimeException('Yield duration with infinite boundary is not possible.');
        }

        return Duration::between($this->getFiniteStart(), $this->getFiniteEnd());
    }

    /**
     * Compares the boundaries (start and end) and also the time axis
     * of this and the other interval.
     */
    public function isEqualTo(self $other): bool
    {
        if (($this->hasInfiniteStart() && !$other->hasInfiniteStart()) ||
            (!$this->hasInfiniteStart() && $other->hasInfiniteStart()) ||
            ($this->hasInfiniteEnd() && !$other->hasInfiniteEnd()) ||
            (!$this->hasInfiniteEnd() && $other->hasInfiniteEnd())) {
            return false;
        }

        return (
                ($this->hasInfiniteStart() && $other->hasInfiniteStart()) ||
                $this->getFiniteStart()->isEqualTo($other->getFiniteStart())
            ) && (
                ($this->hasInfiniteEnd() && $other->hasInfiniteEnd()) ||
                $this->getFiniteEnd()->isEqualTo($other->getFiniteEnd())
            );
    }

    /**
     * Determines if this interval has finite boundaries.
     */
    public function isFinite(): bool
    {
        return !($this->hasInfiniteStart() || $this->hasInfiniteEnd());
    }

    /**
     * Determines if this interval has infinite start boundary.
     */
    public function hasInfiniteStart(): bool
    {
        return null === $this->start;
    }

    /**
     * Determines if this interval has infinite end boundary.
     */
    public function hasInfiniteEnd(): bool
    {
        return null === $this->end;
    }
}
