<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\DayOfWeek;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneRegion;
use Symfony\Component\String\ByteString;

class LocalDateInterval
{
    /**
     * @var LocalDate|null
     */
    private $start;

    /**
     * @var LocalDate|null
     */
    private $end;

    private function __construct(?LocalDate $start, ?LocalDate $end)
    {
        if ($start && $end && $start->isAfter($end)) {
            throw new \InvalidArgumentException("Start after end: ${start} / ${end}");
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Creates a closed interval between given dates.
     */
    public static function between(?LocalDate $start, ?LocalDate $end): self
    {
        return new self($start, $end);
    }

    /**
     * Creates an infinite interval since given start date.
     */
    public static function since(LocalDate $start): self
    {
        return new self($start, null);
    }

    /**
     * Creates an infinite interval until given end date.
     */
    public static function until(LocalDate $end): self
    {
        return new self(null, $end);
    }

    /**
     * Creates a closed interval including only given date.
     */
    public static function atomic(LocalDate $date): self
    {
        return self::between($date, $date);
    }

    /**
     * Obtains the current calendar week based on first day of week.
     */
    public static function ofCurrentWeek(DayOfWeek $firstDate): self
    {
        $date = LocalDate::now(TimeZone::parse('Europe/Zurich'));

        while ($date->getDayOfWeek() !== $firstDate) {
            $date = $date->minusDays(1);
        }

        return self::between($date, $date->plusDays(6));
    }

    /**Time
     * Converts this instance to a timestamp interval with
     * dates from midnight to midnight.
     */
    public function toFullDays(): LocalDateTimeInterval
    {
        /** @var LocalDateTime $start */
        $start = null;

        /** @var LocalDateTime $end */
        $end = null;

        if (!$this->hasInfiniteEnd() && !$this->hasInfiniteStart() && $this->getFiniteEnd()->isEqualTo($this->getFiniteStart())) {
            $end = $this->getFiniteEnd()->atTime(LocalTime::of(23, 59, 59));
        } elseif (!$this->hasInfiniteEnd()) {
            $end = $this->getFiniteEnd()->atTime(LocalTime::of(0, 0, 0));
        }

        if (!$this->hasInfiniteStart()) {
            $start = $this->getFiniteStart()->atTime(LocalTime::of(0, 0, 0));
        }

        return LocalDateTimeInterval::between($start, $end);
    }

    /**
     * Converts this instance to a moment interval with date boundaries mapped
     * to the midnight cycle in given time zone.
     */
    public function inTimeZone(TimeZoneRegion $timezoneId): ZonedDateTimeInterval
    {
        return $this->toFullDays()->atTimeZone($timezoneId);
    }

    /**
     * Yields the length of this interval in days.
     */
    public function getLengthInDays(): int
    {
        if (!$this->isFinite()) {
            throw new \RuntimeException('An infinite interval has no finite duration.');
        }

        return $this->getFiniteStart()->daysUntil($this->getFiniteEnd()) + 1;
    }

    /**
     * Yields the length of this interval in given calendrical units.
     */
    public function getPeriod(): Period
    {
        if (!$this->isFinite()) {
            throw new \RuntimeException('An infinite interval has no finite duration.');
        }

        return Period::between($this->getFiniteStart(), $this->getFiniteEnd());
    }

    /**
     * Moves this interval along the time axis by given units.
     */
    public function move(Period $period): self
    {
        return new self(
            $this->start ? $this->start->plusPeriod($period) : null,
            $this->end ? $this->end->plusPeriod($period) : null
        );
    }

    /**
     * Obtains a stream iterating over every calendar date between given interval boundaries.
     *
     * @return \Traversable<LocalDate>
     */
    public static function iterateDaily(LocalDate $start, LocalDate $end): \Traversable
    {
        $interval = self::between($start, $end);

        if (!$interval->isFinite()) {
            throw new \RuntimeException('Iterate is not supported for infinite interval.');
        }

        return $interval->iterate(Period::of(0, 0, 1));
    }

    /**
     * Obtains a stream iterating over every calendar date which is the result of addition of given duration
     * to start until the end of this interval is reached.
     *
     * @return \Traversable<LocalDate>
     */
    public function iterate(Period $period): \Traversable
    {
        if (!$this->isFinite()) {
            throw new \RuntimeException('Iterate is not supported for infinite interval.');
        }

        for ($start = $this->getFiniteStart(); $start->isBeforeOrEqualTo($this->getFiniteEnd());) {
            yield $start;

            $start = $start->plusPeriod($period);
        }
    }

    /**
     * Obtains a stream iterating over every calendar date of the canonical form of this interval
     * and applies given exclusion filter.
     *
     * @param array<DayOfWeek> $daysExcluded
     *
     * @return \Traversable<LocalDate>
     */
    public function iterateExcluding(array $daysExcluded): \Traversable
    {
        yield from array_filter(
            iterator_to_array(self::iterateDaily($this->getFiniteStart(), $this->getFiniteEnd())),
            function (LocalDate $date) use ($daysExcluded): bool {
                return !in_array($date->getDayOfWeek(), $daysExcluded, true);
            }
        );
    }

    /**
     * Obtains a stream iterating over every calendar date which is the result of addition of given duration
     * in week-based units to start until the end of this interval is reached.
     *
     * @return \Traversable<LocalDate>
     */
    public function iterateWeekBased(int $years, int $weeks, int $days): \Traversable
    {
        if (0 > $years || 0 > $weeks || 0 > $days) {
            throw new \RuntimeException('Found illegal negative duration component.');
        }

        $period = Period::of($years, 0, $weeks * LocalTime::DAYS_PER_WEEK + $days);

        if ($period->isZero()) {
            throw new \RuntimeException('Cannot iterate with an empty Period.');
        }

        return $this->iterate($period);
    }

    /**
     * Interpretes given ISO-conforming text as interval.
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
            $ld1 = null;
        } elseif ($startsWithPeriod) {
            $ld2 = LocalDate::parse($endStr->toString());
            $ld1 = $ld2->minusPeriod(Period::parse($startStr->toString()));

            return self::between($ld1, $ld2);
        } else {
            $ld1 = LocalDate::parse($startStr->toString());
        }

        //END
        if ($endsWithInfinity) {
            $ld2 = null;
        } elseif ($endsWithPeriod) {
            if (null === $ld1) {
                throw new \RuntimeException('Cannot process end period without start.');
            }
            $ld2 = $ld1->plusPeriod(Period::parse($endStr->toString()));
        } else {
            $ld2 = LocalDate::parse($endStr->toString());
        }

        return self::between($ld1, $ld2);
    }

    /**
     * Yields a descriptive string of start and end.
     */
    public function toString(): string
    {
        return sprintf(
            '%s/%s',
            $this->hasInfiniteStart() ? InfinityStyle::SYMBOL : $this->start,
            $this->hasInfiniteEnd() ? InfinityStyle::SYMBOL : $this->end
        );
    }

    public function getStart(): ?LocalDate
    {
        return $this->start;
    }

    public function getEnd(): ?LocalDate
    {
        return $this->end;
    }

    /**
     * Yields a copy of this interval with given start time.
     */
    public function withStart(LocalDate $startDate): self
    {
        return self::between($startDate, $this->end);
    }

    /**
     * Yields a copy of this interval with given end time.
     */
    public function withEnd(LocalDate $endDate): self
    {
        return self::between($this->start, $endDate);
    }

    /**
     * Determines if this interval is empty.
     */
    public function isEmpty(): bool
    {
        if ($this->isFinite()) {
            return 0 === $this->getFiniteStart()->compareTo($this->getFiniteEnd());
        }

        return false;
    }

    /**
     * Is this interval before the given time point?
     */
    public function isBefore(LocalDate $date): bool
    {
        if ($this->hasInfiniteEnd()) {
            return false;
        }

        return $this->getFiniteEnd()->isBefore($date);
    }

    public function isBeforeInterval(self $other): bool
    {
        if ($other->hasInfiniteStart() || $this->hasInfiniteEnd()) {
            return false;
        }

        return $this->getFiniteEnd()->isBefore($other->getFiniteStart());
    }

    /**
     * Is this interval after the other one?
     */
    public function isAfter(LocalDate $date): bool
    {
        if ($this->hasInfiniteStart()) {
            return false;
        }

        return $this->getFiniteStart()->isAfter($date);
    }

    public function isAfterInterval(self $other): bool
    {
        if ($other->hasInfiniteEnd() || $this->hasInfiniteStart()) {
            return false;
        }

        return $this->getFiniteStart()->isAfter($other->getFiniteEnd());
    }

    /**
     * Queries if given time point belongs to this interval.
     */
    public function contains(LocalDate $date): bool
    {
        return ($this->hasInfiniteStart() || !$this->getFiniteStart()->isAfter($date))
            && ($this->hasInfiniteEnd() || $this->getFiniteEnd()->isAfter($date));
    }

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
     * Changes this interval to an empty interval with the same
     * start anchor.
     */
    public function collapse(): self
    {
        if ($this->hasInfiniteStart()) {
            throw new \RuntimeException('An interval with infinite past cannot be collapsed.');
        }

        return self::atomic($this->getFiniteStart());
    }

    /**
     * Compares the boundaries (start and end) and also the time axis
     * of this and the other interval.
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
     * ALLEN-relation: Does this interval precede the other one such that
     * there is a gap between?
     */
    public function precedes(self $other): bool
    {
        if ($this->hasInfiniteEnd() || $other->hasInfiniteStart()) {
            return false;
        }

        $endA = $this->getFiniteEnd();
        $startB = $other->getStart();

        return $startB ? $endA->isBefore($startB) : true;
    }

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
        if ($this->hasInfiniteEnd() || $other->hasInfiniteStart()) {
            return false;
        }

        $endA = $this->getFiniteEnd();
        $startB = $other->getStart();

        return $startB ? $endA->isEqualTo($startB) : true;
    }

    public function metBy(self $other): bool
    {
        return $other->meets($this);
    }

    /**
     * ALLEN-relation: Does this interval overlaps the other one such that
     * the start of this interval is still before the start of the other
     * one?
     */
    public function overlaps(self $other): bool
    {
        return
            (
                $this->hasInfiniteStart() ||
                $other->hasInfiniteEnd() ||
                (
                    $this->getFiniteStart()->isBefore($other->getFiniteEnd()) &&
                    $this->getFiniteStart()->isBefore($other->getFiniteStart())
                )
            ) &&
            (
                $this->hasInfiniteEnd() ||
                $other->hasInfiniteStart() ||
                (
                    $this->getFiniteEnd()->isAfter($other->getFiniteStart()) &&
                    $this->getFiniteEnd()->isBefore($other->getFiniteEnd())
                )
            );
    }

    public function overlappedBy(self $other): bool
    {
        return $other->overlaps($this);
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

    public function enclosedBy(self $other): bool
    {
        return $other->encloses($this);
    }

    /**
     * Queries if this interval intersects the other one such that there is at least one common time point.
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
     * Obtains the intersection of this interval and other one if present.
     */
    public function findIntersection(self $other): ?self
    {
        if ($this->intersects($other)) {
            if ($this->hasInfiniteStart() || $other->hasInfiniteStart()) {
                $start = null;
            } else {
                $start = LocalDate::maxOf($this->getFiniteStart(), $other->getFiniteStart());
            }

            if ($this->hasInfiniteEnd() || $other->hasInfiniteEnd()) {
                $end = null;
            } else {
                $end = LocalDate::minOf($this->getFiniteEnd(), $other->getFiniteEnd());
            }

            return self::between($start, $end);
        }

        return null;
    }

    /**
     * Queries if this interval abuts the other one such that there is neither any overlap nor any gap between.
     */
    public function abuts(self $other): bool
    {
        return (bool) ($this->meets($other) ^ $this->metBy($other));
    }

    public function hasInfiniteStart(): bool
    {
        return null === $this->start;
    }

    public function hasInfiniteEnd(): bool
    {
        return null === $this->end;
    }

    public function isFinite(): bool
    {
        return !($this->hasInfiniteStart() || $this->hasInfiniteEnd());
    }

    public function getFiniteEnd(): LocalDate
    {
        if (null === $this->end) {
            throw new \RuntimeException('This interval has a non finite end.');
        }

        return $this->end;
    }

    public function getFiniteStart(): LocalDate
    {
        if (null === $this->start) {
            throw new \RuntimeException('This interval has a non finite start.');
        }

        return $this->start;
    }
}
