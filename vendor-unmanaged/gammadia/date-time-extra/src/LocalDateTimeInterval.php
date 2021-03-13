<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;
use Symfony\Component\String\ByteString;
use Traversable;
use Webmozart\Assert\Assert;
use function Gammadia\Collections\Functional\contains;
use function Gammadia\Collections\Functional\map;

class LocalDateTimeInterval
{
    /**
     * @var LocalDateTime|null
     */
    private $start;

    /**
     * @var LocalDateTime|null
     */
    private $end;

    private function __construct(?LocalDateTime $start, ?LocalDateTime $end)
    {
        if ($start && $end && $start->isAfter($end)) {
            throw new \InvalidArgumentException("Start after end: ${start} / ${end}");
        }

        $this->start = $start;
        $this->end = $end;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Creates a finite half-open interval between given time points.
     *
     * @param LocalDateTime $start the local datetime of lower boundary (inclusive)
     * @param LocalDateTime $end the local datetime of upper boundary (exclusive)
     *
     * @return self A LocalDateTimeInterval interval
     */
    public static function between(?LocalDateTime $start, ?LocalDateTime $end): self
    {
        return new self($start, $end);
    }

    public static function empty(LocalDateTime $at): self
    {
        return self::between($at, $at);
    }

    /**
     * Creates an infinite half-open interval since given start.
     *
     * @param LocalDateTime $start the local datetime of lower boundary (inclusive)
     *
     * @return self new local datetime interval
     */
    public static function since(LocalDateTime $start): self
    {
        return new self($start, null);
    }

    /**
     * Creates an infinite open interval until given end.
     *
     * @param LocalDateTime $end the local datetime of upper boundary (exclusive)
     *
     * @return self new timestamp interval
     */
    public static function until(LocalDateTime $end): self
    {
        return new self(null, $end);
    }

    /**
     * Creates an infinite interval.
     */
    public static function forever(): self
    {
        return new self(null, null);
    }

    /**
     * @param LocalDate|LocalDateTime $day
     */
    public static function day($day): self
    {
        Assert::isInstanceOfAny($day, [LocalDate::class, LocalDateTime::class]);

        if ($day instanceof LocalDateTime) {
            $startOfDay = $day->withTime(LocalTime::min());
        } else {
            $startOfDay = $day->atTime(LocalTime::min());
        }

        return new self($startOfDay, $startOfDay->plusDays(1));
    }

    /**
     * Creates an interval that contains (encompasses) every provided intervals
     *
     * @param self ...$localDateTimeIntervals
     *
     * @return self|null new timestamp interval or null if the input is empty
     */
    public static function containerOf(self ...$localDateTimeIntervals): ?self
    {
        if (empty($localDateTimeIntervals)) {
            return null;
        }

        $starts = map($localDateTimeIntervals, static function (self $localDateTimeInterval): ?LocalDateTime {
            return $localDateTimeInterval->getStart();
        });
        $ends = map($localDateTimeIntervals, static function (self $localDateTimeInterval): ?LocalDateTime {
            return $localDateTimeInterval->getEnd();
        });

        return self::between(
            contains($starts, null, true) ? null : LocalDateTime::minOf(...$starts),
            contains($ends, null, true) ? null : LocalDateTime::maxOf(...$ends)
        );
    }

    /**
     * Converts this instance to a timestamp interval with
     * dates from midnight to midnight.
     */
    public function toFullDays(): self
    {
        return self::between(
            $this->hasInfiniteStart() ? null : $this->getFiniteStart()->withTime(LocalTime::min()),
            $this->hasInfiniteEnd() ? null : (
                $this->getFiniteEnd()->getTime()->isEqualTo(LocalTime::min()) &&
                // This allows to deal with empty ranges
                !$this->getFiniteEnd()->isEqualTo($this->getFiniteStart())
                    ? $this->getFiniteEnd()
                    : $this->getFiniteEnd()->plusDays(1)->withTime(LocalTime::min())
            )
        );
    }

    public function isFullDays(): bool
    {
        return $this->isEqualTo($this->toFullDays());
    }

    /**
     * Returns the nullable start time point.
     */
    public function getStart(): ?LocalDateTime
    {
        return $this->start;
    }

    /**
     * Returns the nullable end time point.
     */
    public function getEnd(): ?LocalDateTime
    {
        return $this->end;
    }

    public function getInclusiveEnd(): ?LocalDateTime
    {
        return $this->hasInfiniteEnd() ? null : $this->getFiniteEnd()->minusNanos(1);
    }

    /**
     * Yields the start time point if not null.
     */
    public function getFiniteStart(): LocalDateTime
    {
        if (null === $this->start) {
            throw new \RuntimeException('This interval has a non finite start.');
        }

        return $this->start;
    }

    /**
     * Yields the start time point if not null.
     */
    public function getFiniteEnd(): LocalDateTime
    {
        if (null === $this->end) {
            throw new \RuntimeException('This interval has an non finite end.');
        }

        return $this->end;
    }

    /**
     * Yields a copy of this interval with given start time.
     */
    public function withStart(LocalDateTime $t): self
    {
        return self::between($t, $this->end);
    }

    /**
     * Yields a copy of this interval with given end time.
     */
    public function withEnd(LocalDateTime $t): self
    {
        return self::between($this->start, $t);
    }

    /**
     * Returns a string representation of this interval.
     */
    public function toString(): string
    {
        return sprintf(
            '%s/%s',
            $this->hasInfiniteStart() ? InfinityStyle::SYMBOL : $this->start,
            $this->hasInfiniteEnd() ? InfinityStyle::SYMBOL : $this->end
        );
    }

    /**
     * Combines this local datetime interval with the timezone offset UTC+00:00 to a global UTC-interval.
     *
     * @return InstantInterval global timestamp interval interpreted at offset UTC+00:00
     */
    public function atUTC(): InstantInterval
    {
        return InstantInterval::between(
            $this->start ? $this->start->atTimeZone(TimeZoneOffset::utc())->getInstant() : null,
            $this->end ? $this->end->atTimeZone(TimeZoneOffset::utc())->getInstant() : null
        );
    }

    /**
     * Combines this local timestamp interval with given timezone to a ZonedInterval.
     *
     * @param TimeZoneRegion $timezoneId timezone id
     *
     * @return ZonedDateTimeInterval zoned datetime interval interpreted in given timezone
     */
    public function atTimeZone(TimeZoneRegion $timezoneId): ZonedDateTimeInterval
    {
        return ZonedDateTimeInterval::between(
            $this->start ? $this->start->atTimeZone($timezoneId) : null,
            $this->end ? $this->end->atTimeZone($timezoneId) : null
        );
    }

    /**
     * Parses the given text as as interval.
     *
     * @param string $text text to be parsed
     */
    public static function parse(string $text): self
    {
        [$startStr, $endStr] = explode('/', trim($text), 2);

        $startStr = new ByteString($startStr);
        $endStr = new ByteString($endStr);

        $startsWithPeriod = $startStr->startsWith('P');
        $startsWithInfinity = $startStr->equalsTo(InfinityStyle::SYMBOL);

        $endsWithPeriod = $endStr->startsWith('P');
        $endsWithInfinity = $endStr->equalsTo(InfinityStyle::SYMBOL);

        if ($startsWithPeriod && $endsWithPeriod) {
            throw IntervalParseException::uniqueDuration($text);
        }

        if (($startsWithPeriod && $endsWithInfinity) ||
            ($startsWithInfinity && $endsWithPeriod)
        ) {
            throw IntervalParseException::durationIncompatibleWithInfinity($text);
        }

        //START
        if ($startsWithInfinity) {
            $ldt1 = null;
        } elseif ($startsWithPeriod) {
            $ldt2 = LocalDateTime::parse($endStr->toString());
            $ldt1 = $startStr->indexOf('T')
                ? $ldt2->minusDuration(Duration::parse($startStr->toString()))
                : $ldt2->minusPeriod(Period::parse($startStr->toString()));

            return self::between($ldt1, $ldt2);
        } else {
            $ldt1 = LocalDateTime::parse($startStr->toString());
        }

        //END
        if ($endsWithInfinity) {
            $ldt2 = null;
        } elseif ($endsWithPeriod) {
            if (null === $ldt1) {
                throw new \RuntimeException('Cannot process end period without start.');
            }
            $ldt2 = $endStr->indexOf('T')
                ? $ldt1->plusDuration(Duration::parse($endStr->toString()))
                : $ldt1->plusPeriod(Period::parse($endStr->toString()));
        } else {
            $ldt2 = LocalDateTime::parse($endStr->toString());
        }

        return self::between($ldt1, $ldt2);
    }

    /**
     * Moves this interval along the POSIX-axis by the given duration or period.
     *
     * @param Duration|Period $periodOrDuration
     *
     * @return self moved copy of this interval
     */
    public function move($periodOrDuration): self
    {
        /** @var Period|Duration|mixed $periodOrDuration (for static analysis)) */
        if ($periodOrDuration instanceof Period) {
            return new self(
                $this->start ? $this->start->plusPeriod($periodOrDuration) : null,
                $this->end ? $this->end->plusPeriod($periodOrDuration) : null
            );
        }

        if ($periodOrDuration instanceof Duration) {
            return new self(
                $this->start ? $this->start->plusDuration($periodOrDuration) : null,
                $this->end ? $this->end->plusDuration($periodOrDuration) : null
            );
        }

        throw new \RuntimeException('The given value must be either Duration or Period.');
    }

    /**
     * Return the length of this interval and applies a timezone offset correction.
     *
     * @return Duration duration including a zonal correction
     */
    public function getDuration(): Duration
    {
        if (!$this->isFinite()) {
            throw new \RuntimeException('Returning the duration with infinite boundary is not possible.');
        }

        return $this->atUTC()->getDuration();
    }

    /**
     * Iterates through every moments which are the result of adding the given duration or period
     * to the start until the end of this interval is reached.
     *
     * @param Period|Duration $periodOrDuration
     *
     * @return Traversable<LocalDateTime>
     */
    public function iterate($periodOrDuration): Traversable
    {
        /** @var Period|Duration|mixed $periodOrDuration (for static analysis)) */
        if (!$this->isFinite()) {
            throw new \RuntimeException('Iterate is not supported for infinite intervals.');
        }

        if (!($periodOrDuration instanceof Period || $periodOrDuration instanceof Duration)) {
            throw new \RuntimeException('Instance of Duration or Period expected.');
        }

        for ($start = $this->getFiniteStart(); $start->isBefore($this->getFiniteEnd());) {
            yield $start;

            $start = $periodOrDuration instanceof Period
                ? $start->plusPeriod($periodOrDuration)
                : $start->plusDuration($periodOrDuration);
        }
    }

    /**
     * @return Traversable<self>
     */
    public function days(): Traversable
    {
        return $this->toFullDays()->slice(Period::ofDays(1));
    }

    /**
     * Returns slices of this interval.
     *
     * Each slice is at most as long as the given period or duration. The last slice might be shorter.
     *
     * @param Period|Duration $periodOrDuration
     *
     * @return Traversable<self>
     */
    public function slice($periodOrDuration): Traversable
    {
        foreach ($this->iterate($periodOrDuration) as $start) {
            $end = $periodOrDuration instanceof Period
                ? $start->plusPeriod($periodOrDuration)
                : $start->plusDuration($periodOrDuration);

            yield self::between($start, LocalDateTime::minOf($end, $this->getFiniteEnd()));
        }
    }

    /**
     * Determines if this interval is empty. An interval is empty when the "end" is equal to the "start" boundary.
     */
    public function isEmpty(): bool
    {
        if ($this->isFinite()) {
            return 0 === $this->getFiniteStart()->compareTo($this->getFiniteEnd());
        }

        return false;
    }

    /**
     * Is the finite end of this interval before or equal to the given local datetime.
     */
    public function isBefore(LocalDateTime $t): bool
    {
        if ($this->hasInfiniteEnd()) {
            return false;
        }

        return $this->getFiniteEnd()->isBeforeOrEqualTo($t);
    }

    /**
     * Is the finite end of this interval before or equal to the finite start of the given interval.
     */
    public function isBeforeInterval(self $other): bool
    {
        if ($other->hasInfiniteStart() || $this->hasInfiniteEnd()) {
            return false;
        }

        $endA = $this->getFiniteEnd();
        $startB = $other->getFiniteStart();

        return $endA->isBeforeOrEqualTo($startB);
    }

    /**
     * Is the finite start end of this interval after the given local datetime.
     */
    public function isAfter(LocalDateTime $t): bool
    {
        if ($this->hasInfiniteStart()) {
            return false;
        }

        return $this->getFiniteStart()->isAfter($t);
    }

    /**
     * Is the finite start of this interval after or equal to the finite end of the given interval.
     */
    public function isAfterInterval(self $other): bool
    {
        return $other->isBeforeInterval($this);
    }

    /**
     * Queries if given time point belongs to this interval.
     */
    public function contains(LocalDateTime $t): bool
    {
        return ($this->hasInfiniteStart() || !$this->getFiniteStart()->isAfter($t))
            && ($this->hasInfiniteEnd() || $this->getFiniteEnd()->isAfter($t));
    }

    /**
     * Does this interval contain the other one?
     */
    public function containsInterval(self $other): bool
    {
        if (!$other->isFinite()) {
            return false;
        }

        if ($this->hasInfiniteStart() && $other->getFiniteEnd()->isBeforeOrEqualTo($this->getFiniteEnd())) {
            return true;
        }

        if ($this->hasInfiniteStart() && !$other->getFiniteEnd()->isBeforeOrEqualTo($this->getFiniteEnd())) {
            return false;
        }

        if ($this->hasInfiniteEnd() && $other->getFiniteStart()->isAfterOrEqualTo($this->getFiniteStart())) {
            return true;
        }

        if ($this->hasInfiniteEnd() && !$other->getFiniteStart()->isAfterOrEqualTo($this->getFiniteStart())) {
            return false;
        }

        if ($other->getFiniteStart()->isAfterOrEqualTo($this->getFiniteStart()) &&
            $other->getFiniteEnd()->isBeforeOrEqualTo($this->getFiniteEnd())) {
            return true;
        }

        return false;
    }

    /**
     * ALLEN-relation: Does this interval precede the other one such that
     * there is a gap between?
     */
    public function precedes(self $other): bool
    {
        if ($other->hasInfiniteStart() || $this->hasInfiniteEnd()) {
            return false;
        }

        $endA = $this->getFiniteEnd();
        $startB = $other->getStart();

        if (null === $startB) {
            return true;
        }

        return $endA->isBefore($startB);
    }

    /**
     * ALLEN-relation: Equivalent to $other->precedes($this).
     */
    public function precededBy(self $other): bool
    {
        return $other->precedes($this);
    }

    /**
     * ALLEN-relation: Does this interval precede the other one such that
     * there is no gap between?
     */
    public function meets(self $other): bool
    {
        if ($other->hasInfiniteStart() || $this->hasInfiniteEnd()) {
            return false;
        }

        return $this->getFiniteEnd()->isEqualTo($other->getFiniteStart());
    }

    /**
     * ALLEN-relation: Equivalent to $other->meets($this).
     */
    public function metBy(self $other): bool
    {
        return $other->meets($this);
    }

    /**
     * ALLEN-relation: Does this interval finish the other one such that
     * both end time points are equal and the start of this interval is after
     * the start of the other one?
     */
    public function finishes(self $other): bool
    {
        if ((null === $this->end && null !== $other->end) ||
            (null !== $this->end && null === $other->end)) {
            return false;
        }

        if ($this->end && $other->end && !$this->end->isEqualTo($other->end)) {
            return false;
        }

        if ($other->hasInfiniteStart()) {
            return true;
        }

        if ($this->hasInfiniteStart()) {
            return false;
        }

        return $other->getFiniteStart()->isBefore($this->getFiniteStart());
    }

    /**
     * ALLEN-relation: Equivalent to $other->finishes($this).
     */
    public function finishedBy(self $other): bool
    {
        return $other->finishes($this);
    }

    /**
     * ALLEN-relation: Does this interval start the other one such that both
     * start time points are equal and the end of this interval is before the
     * end of the other one?
     */
    public function starts(self $other): bool
    {
        if ((null === $this->start && null !== $other->start) ||
            (null !== $this->start && null === $other->start)) {
            return false;
        }

        if ($this->start && $other->start && !$this->start->isEqualTo($other->start)) {
            return false;
        }

        if ($other->hasInfiniteEnd() && !$this->hasInfiniteEnd()) {
            return true;
        }

        if ($this->hasInfiniteEnd()) {
            return false;
        }

        return $other->getFiniteEnd()->isAfter($this->getFiniteEnd());
    }

    /**
     * ALLEN-relation: Equivalent to $other->starts($this).
     */
    public function startedBy(self $other): bool
    {
        return $other->starts($this);
    }

    /**
     * ALLEN-relation: Does this interval enclose the other one such that
     * this start is before the start of the other one and this end is after
     * the end of the other one?
     */
    public function encloses(self $other): bool
    {
        if (!$other->isFinite()) {
            return false;
        }

        if ($this->hasInfiniteStart() && $other->getFiniteEnd()->isBefore($this->getFiniteEnd())) {
            return true;
        }

        if ($this->hasInfiniteStart() && !$other->getFiniteEnd()->isBefore($this->getFiniteEnd())) {
            return false;
        }

        if ($this->hasInfiniteEnd() && $other->getFiniteStart()->isAfter($this->getFiniteStart())) {
            return true;
        }

        if ($this->hasInfiniteEnd() && !$other->getFiniteStart()->isAfter($this->getFiniteStart())) {
            return false;
        }

        if ($other->getFiniteStart()->isAfter($this->getFiniteStart()) &&
            $other->getFiniteEnd()->isBefore($this->getFiniteEnd())
        ) {
            return true;
        }

        return false;
    }

    /**
     * ALLEN-relation: Equivalent to $other->encloses($this).
     */
    public function enclosedBy(self $other): bool
    {
        return $other->encloses($this);
    }

    /**
     * ALLEN-relation: Does this interval overlaps the other one such that
     * the start of this interval is still before the start of the other
     * one?
     */
    public function overlaps(self $other): bool
    {
        return

                !($this->hasInfiniteEnd() || $other->hasInfiniteStart()) &&
                ($this->hasInfiniteStart() || $this->getFiniteStart()->isBefore($other->getFiniteStart())) &&
                ($other->hasInfiniteEnd() || $this->getFiniteEnd()->isBefore($other->getFiniteEnd())) &&
                $this->getFiniteEnd()->isAfter($other->getFiniteStart())
            ;
    }

    /**
     * ALLEN-relation: Equivalent to $other->overlaps($this).
     */
    public function overlappedBy(self $other): bool
    {
        return $other->overlaps($this);
    }

    /**
     * Queries if this interval intersects the other one such that there is at least one common time point.
     *
     * @param self $other another interval which might have an intersection with this interval
     *
     * @return bool "true" if there is an non-empty intersection of this interval and the other one else "false"
     */
    public function intersects(self $other): bool
    {
        return
            (
                $this->hasInfiniteStart() ||
                $other->hasInfiniteEnd() ||
                $this->getFiniteStart()->isBefore($other->getFiniteEnd())) &&
            (
                $this->hasInfiniteEnd() ||
                $other->hasInfiniteStart() ||
                $this->getFiniteEnd()->isAfter($other->getFiniteStart())
            );
    }

    /**
     * Queries if this interval abuts the other one such that there is neither any overlap nor any gap between.
     *
     * Equivalent to the expression {@code this.meets(other) ^ this.metBy(other)}. Empty intervals never abut.
     */
    public function abuts(self $other): bool
    {
        return (bool) ($this->meets($other) ^ $this->metBy($other));
    }

    /**
     * Changes this interval to an empty interval with the same
     * start anchor.
     *
     * @return self empty interval with same start (anchor always inclusive)
     */
    public function collapse(): self
    {
        if ($this->hasInfiniteStart()) {
            throw new \RuntimeException('An interval with infinite start cannot be collapsed.');
        }

        return self::between($this->start, $this->start);
    }

    /**
     * Obtains the intersection of this interval and other one if present.
     *
     * @param self $other another interval which might have an intersection with this interval
     *
     * @return  self|null wrapper around the found intersection or null
     */
    public function findIntersection(self $other): ?self
    {
        if ($this->intersects($other)) {
            if ($this->hasInfiniteStart() && $other->hasInfiniteStart()) {
                $start = null;
            } elseif ($this->hasInfiniteStart()) {
                $start = $other->getFiniteStart();
            } elseif ($other->hasInfiniteStart()) {
                $start = $this->getFiniteStart();
            } else {
                $start = LocalDateTime::maxOf($this->getFiniteStart(), $other->getFiniteStart());
            }

            if ($this->hasInfiniteEnd() && $other->hasInfiniteEnd()) {
                $end = null;
            } elseif ($this->hasInfiniteEnd()) {
                $end = $other->getFiniteEnd();
            } elseif ($other->hasInfiniteEnd()) {
                $end = $this->getFiniteEnd();
            } else {
                $end = LocalDateTime::minOf($this->getFiniteEnd(), $other->getFiniteEnd());
            }

            return self::between($start, $end);
        }

        return null;
    }

    /**
     * Compares the boundaries (start and end) of this and the other interval.
     */
    public function isEqualTo(self $other): bool
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

    public function compareTo(self $other): int
    {
        if ($this->hasInfiniteStart()) {
            if (!$other->hasInfiniteStart()) {
                return -1;
            }
        } elseif ($other->hasInfiniteStart()) {
            return 1;
        } else {
            $order = $this->getFiniteStart()->compareTo($other->getFiniteStart());
            if (0 !== $order) {
                return $order;
            }
        }
        // At this point, both intervals have the same start

        if ($this->hasInfiniteEnd()) {
            return $other->hasInfiniteEnd() ? 0 : 1;
        }
        if ($other->hasInfiniteEnd()) {
            return -1;
        }

        return $this->getFiniteEnd()->compareTo($other->getFiniteEnd());
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
