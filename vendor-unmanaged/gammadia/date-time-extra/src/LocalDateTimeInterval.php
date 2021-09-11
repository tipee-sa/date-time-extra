<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZoneOffset;
use Doctrine\ORM\Mapping as ORM;
use Gammadia\DateTimeExtra\Exceptions\IntervalParseException;
use JsonSerializable;
use RuntimeException;
use Stringable;
use Traversable;
use Webmozart\Assert\Assert;
use function Gammadia\Collections\Functional\contains;
use function Gammadia\Collections\Functional\filter;
use function Gammadia\Collections\Functional\map;

#[ORM\Embeddable]
final class LocalDateTimeInterval implements JsonSerializable, Stringable
{
    private function __construct(
        #[ORM\Column(type: 'local_datetime')]
        private ?LocalDateTime $start,

        #[ORM\Column(type: 'local_datetime')]
        private ?LocalDateTime $end,
    ) {
        Assert::false($start && $end && $start->isAfter($end), sprintf('Start after end: %s / %s', $start, $end));
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * Creates a finite half-open interval between given time points (inclusive start, exclusive end).
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
     * Creates an infinite half-open interval since given start (inclusive).
     */
    public static function since(LocalDateTime $start): self
    {
        return new self($start, null);
    }

    /**
     * Creates an infinite open interval until given end (exclusive).
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

    public static function day(LocalDate|LocalDateTime $day): self
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
     * Returns new timestamp interval or null if the input is empty
     */
    public static function containerOf(self ...$localDateTimeIntervals): ?self
    {
        if (empty($localDateTimeIntervals)) {
            return null;
        }

        $starts = map($localDateTimeIntervals, static fn (self $localDateTimeInterval): ?LocalDateTime => $localDateTimeInterval->getStart());
        $ends = map($localDateTimeIntervals, static fn (self $localDateTimeInterval): ?LocalDateTime => $localDateTimeInterval->getEnd());

        return self::between(
            contains($starts, value: null) ? null : LocalDateTime::minOf(...$starts),
            contains($ends, value: null) ? null : LocalDateTime::maxOf(...$ends),
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
                !$this->isEmpty() && $this->getFiniteEnd()->getTime()->isEqualTo(LocalTime::min())
                    ? $this->getFiniteEnd()
                    : $this->getFiniteEnd()->plusDays(1)->withTime(LocalTime::min())
            ),
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
            throw new RuntimeException(sprintf('The interval "%s" does not have a finite start.', $this));
        }

        return $this->start;
    }

    /**
     * Yields the end time point if not null.
     */
    public function getFiniteEnd(): LocalDateTime
    {
        if (null === $this->end) {
            throw new RuntimeException(sprintf('The interval "%s" does not have a finite end.', $this));
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
            $this->hasInfiniteEnd() ? InfinityStyle::SYMBOL : $this->end,
        );
    }

    /**
     * Parses the given text as as interval.
     */
    public static function parse(string $text): self
    {
        [$startStr, $endStr] = explode('/', trim($text), 2);

        $startsWithPeriod = str_starts_with($startStr, 'P');
        $startsWithInfinity = InfinityStyle::SYMBOL === $startStr;

        $endsWithPeriod = str_starts_with($endStr, 'P');
        $endsWithInfinity = InfinityStyle::SYMBOL === $endStr;

        if ($startsWithPeriod && $endsWithPeriod) {
            throw IntervalParseException::uniqueDuration($text);
        }

        if (($startsWithPeriod && $endsWithInfinity) || ($startsWithInfinity && $endsWithPeriod)) {
            throw IntervalParseException::durationIncompatibleWithInfinity($text);
        }

        //START
        if ($startsWithInfinity) {
            $ldt1 = null;
        } elseif ($startsWithPeriod) {
            $ldt2 = LocalDateTime::parse($endStr);
            $ldt1 = str_contains($startStr, 'T')
                ? $ldt2->minusDuration(Duration::parse($startStr))
                : $ldt2->minusPeriod(Period::parse($startStr));

            return self::between($ldt1, $ldt2);
        } else {
            $ldt1 = LocalDateTime::parse($startStr);
        }

        //END
        if ($endsWithInfinity) {
            $ldt2 = null;
        } elseif ($endsWithPeriod) {
            if (null === $ldt1) {
                throw new RuntimeException('Cannot process end period without start.');
            }
            $ldt2 = str_contains($endStr, 'T')
                ? $ldt1->plusDuration(Duration::parse($endStr))
                : $ldt1->plusPeriod(Period::parse($endStr));
        } else {
            $ldt2 = LocalDateTime::parse($endStr);
        }

        return self::between($ldt1, $ldt2);
    }

    /**
     * Moves this interval along the POSIX-axis by the given duration or period.
     */
    public function move(Duration|Period $periodOrDuration): self
    {
        if ($periodOrDuration instanceof Period) {
            return new self(
                $this->start ? $this->start->plusPeriod($periodOrDuration) : null,
                $this->end ? $this->end->plusPeriod($periodOrDuration) : null,
            );
        }

        return new self(
            $this->start ? $this->start->plusDuration($periodOrDuration) : null,
            $this->end ? $this->end->plusDuration($periodOrDuration) : null,
        );
    }

    /**
     * Return the length of this interval and applies a timezone offset correction.
     *
     * Returns duration including a zonal correction.
     */
    public function getDuration(): Duration
    {
        if (!$this->isFinite()) {
            throw new RuntimeException('Returning the duration with infinite boundary is not possible.');
        }

        return Duration::between(
            $this->getFiniteStart()->atTimeZone(TimeZoneOffset::utc())->getInstant(),
            $this->getFiniteEnd()->atTimeZone(TimeZoneOffset::utc())->getInstant(),
        );
    }

    /**
     * Iterates through every moments which are the result of adding the given duration or period
     * to the start until the end of this interval is reached.
     *
     * @return Traversable<LocalDateTime>
     */
    public function iterate(Duration|Period $periodOrDuration): Traversable
    {
        if (!$this->isFinite()) {
            throw new RuntimeException('Iterate is not supported for infinite intervals.');
        }

        for (
            $start = $this->getFiniteStart();
            $start->isBefore($this->getFiniteEnd());
        ) {
            yield $start;

            $start = $periodOrDuration instanceof Period
                ? $start->plusPeriod($periodOrDuration)
                : $start->plusDuration($periodOrDuration);
        }
    }

    /**
     * @return LocalDate[]
     */
    public function days(): array
    {
        $dateRange = LocalDateInterval::containerOf($this);
        Assert::notNull($dateRange);

        return $dateRange->days();
    }

    /**
     * Returns slices of this interval.
     *
     * Each slice is at most as long as the given period or duration. The last slice might be shorter.
     *
     * @return Traversable<self>
     */
    public function slice(Duration|Period $periodOrDuration): Traversable
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
    public function isBefore(LocalDateTime $timepoint): bool
    {
        if ($this->hasInfiniteEnd()) {
            return false;
        }

        return $this->getFiniteEnd()->isBeforeOrEqualTo($timepoint);
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
    public function isAfter(LocalDateTime $timepoint): bool
    {
        if ($this->hasInfiniteStart()) {
            return false;
        }

        return $this->getFiniteStart()->isAfter($timepoint);
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
    public function contains(LocalDateTime $timepoint): bool
    {
        return ($this->hasInfiniteStart() || !$this->getFiniteStart()->isAfter($timepoint))
            && ($this->hasInfiniteEnd() || $this->getFiniteEnd()->isAfter($timepoint));
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
            (null !== $this->end && null === $other->end)
        ) {
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
            (null !== $this->start && null === $other->start)
        ) {
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
        return !($this->hasInfiniteEnd() || $other->hasInfiniteStart())
            && ($this->hasInfiniteStart() || $this->getFiniteStart()->isBefore($other->getFiniteStart()))
            && ($other->hasInfiniteEnd() || $this->getFiniteEnd()->isBefore($other->getFiniteEnd()))
            && $this->getFiniteEnd()->isAfter($other->getFiniteStart());
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
     * Returns "true" if there is an non-empty intersection of this interval and the other one else "false"
     */
    public function intersects(self $other): bool
    {
        if ($this->isEmpty()) {
            return $other->contains($this->getFiniteStart());
        }
        if ($other->isEmpty()) {
            return $this->contains($other->getFiniteStart());
        }

        return ($this->hasInfiniteStart() || $other->hasInfiniteEnd() || $this->getFiniteStart()->isBefore($other->getFiniteEnd()))
            && ($this->hasInfiniteEnd() || $other->hasInfiniteStart() || $this->getFiniteEnd()->isAfter($other->getFiniteStart()));
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
     * Returns empty interval with same start (anchor always inclusive)
     */
    public function collapse(): self
    {
        if ($this->hasInfiniteStart()) {
            throw new RuntimeException('An interval with infinite start cannot be collapsed.');
        }

        return self::between($this->start, $this->start);
    }

    /**
     * Null can come as input from the result of {@see containerOf()}
     */
    public function expand(?self ...$others): self
    {
        // Filter out null results
        $others = filter($others);
        if (empty($others)) {
            return $this;
        }

        $expanded = self::containerOf($this, ...$others);
        Assert::notNull($expanded);

        return $expanded;
    }

    /**
     * Obtains the intersection of this interval and other one if present.
     *
     * Returns a wrapper around the found intersection or null
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
            $this->hasInfiniteEnd() !== $other->hasInfiniteEnd()
        ) {
            return false;
        }

        return ($this->hasInfiniteStart() || $this->getFiniteStart()->isEqualTo($other->getFiniteStart()))
            && ($this->hasInfiniteEnd() || $this->getFiniteEnd()->isEqualTo($other->getFiniteEnd()));
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
