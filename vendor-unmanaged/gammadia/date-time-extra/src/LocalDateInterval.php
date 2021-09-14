<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Period;
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
final class LocalDateInterval implements JsonSerializable, Stringable
{
    private function __construct(
        #[ORM\Column(type: 'local_date')]
        private ?LocalDate $start,

        #[ORM\Column(type: 'local_date')]
        private ?LocalDate $end,
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

    public static function forever(): self
    {
        return self::between(null, null);
    }

    /**
     * Creates an interval that contains (encompasses) every provided intervals
     *
     * Returns new timestamp interval or null if the input is empty
     */
    public static function containerOf(self|LocalDateTimeInterval ...$localDateIntervals): ?self
    {
        if (empty($localDateIntervals)) {
            return null;
        }

        $localDateIntervals = map($localDateIntervals, static function ($localDateOrDateTimeInterval): self {
            if ($localDateOrDateTimeInterval instanceof LocalDateTimeInterval) {
                $timeRange = $localDateOrDateTimeInterval->toFullDays();

                return self::between(
                    $timeRange->hasInfiniteStart() ? null : $timeRange->getFiniteStart()->getDate(),
                    $timeRange->hasInfiniteEnd() ? null : $timeRange->getFiniteEnd()->getDate()->minusDays(1),
                );
            }

            return $localDateOrDateTimeInterval;
        });

        $starts = map($localDateIntervals, static fn (self $localDateInterval): ?LocalDate => $localDateInterval->getStart());
        $ends = map($localDateIntervals, static fn (self $localDateInterval): ?LocalDate => $localDateInterval->getEnd());

        return self::between(
            contains($starts, value: null) ? null : LocalDate::minOf(...$starts),
            contains($ends, value: null) ? null : LocalDate::maxOf(...$ends),
        );
    }

    /**
     * Null can come as input from the result of {@see containerOf()}
     */
    public function expand(self|LocalDateTimeInterval|null ...$others): self
    {
        $others = filter($others);
        if (empty($others)) {
            return $this;
        }
        Assert::allIsInstanceOfAny($others, [self::class, LocalDateTimeInterval::class]);

        $expanded = self::containerOf($this, ...$others);
        Assert::notNull($expanded);

        return $expanded;
    }

    /**
     * Converts this instance to a timestamp interval with
     * dates from midnight to midnight.
     */
    public function toLocalDateTimeInterval(): LocalDateTimeInterval
    {
        return LocalDateTimeInterval::between(
            $this->hasInfiniteStart() ? null : $this->getFiniteStart()->atTime(LocalTime::min()),
            $this->hasInfiniteEnd() ? null : $this->getFiniteEnd()->atTime(LocalTime::min())->plusDays(1),
        );
    }

    /**
     * Yields the length of this interval in days.
     */
    public function getLengthInDays(): int
    {
        if (!$this->isFinite()) {
            throw new RuntimeException('An infinite interval has no finite duration.');
        }

        return $this->getFiniteStart()->daysUntil($this->getFiniteEnd()) + 1;
    }

    /**
     * Yields the length of this interval in given calendrical units.
     */
    public function getPeriod(): Period
    {
        if (!$this->isFinite()) {
            throw new RuntimeException('An infinite interval has no finite duration.');
        }

        return Period::between($this->getFiniteStart(), $this->getFiniteEnd()->plusDays(1));
    }

    /**
     * Moves this interval along the time axis by given units.
     */
    public function move(Period $period): self
    {
        return new self(
            $this->start ? $this->start->plusPeriod($period) : null,
            $this->end ? $this->end->plusPeriod($period) : null,
        );
    }

    /**
     * Obtains a stream iterating over every calendar date between given interval boundaries.
     *
     * @return Traversable<LocalDate>
     */
    public static function iterateDaily(LocalDate $start, LocalDate $end): Traversable
    {
        $interval = self::between($start, $end);

        return $interval->iterate(Period::ofDays(1));
    }

    /**
     * Obtains a stream iterating over every calendar date which is the result of addition of given duration
     * to start until the end of this interval is reached.
     *
     * @return Traversable<LocalDate>
     */
    public function iterate(Period $period): Traversable
    {
        if (!$this->isFinite()) {
            throw new RuntimeException('Iterate is not supported for infinite interval.');
        }

        for ($start = $this->getFiniteStart();
             $start->isBeforeOrEqualTo($this->getFiniteEnd());
             $start = $start->plusPeriod($period)
        ) {
            yield $start;
        }
    }

    /**
     * @return LocalDate[]
     */
    public function days(): array
    {
        return iterator_to_array($this->iterate(Period::ofDays(1)), false);
    }

    /**
     * Interpretes given ISO-conforming text as interval.
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
            $ld1 = null;
        } elseif ($startsWithPeriod) {
            $ld2 = LocalDate::parse($endStr);
            $ld1 = $ld2->minusPeriod(Period::parse($startStr));

            return self::between($ld1, $ld2);
        } else {
            $ld1 = LocalDate::parse($startStr);
        }

        //END
        if ($endsWithInfinity) {
            $ld2 = null;
        } elseif ($endsWithPeriod) {
            if (null === $ld1) {
                throw new RuntimeException('Cannot process end period without start.');
            }
            $ld2 = $ld1->plusPeriod(Period::parse($endStr));
        } else {
            $ld2 = LocalDate::parse($endStr);
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
            $this->getStartIso(),
            $this->getEndIso(),
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

    public function getStartIso(): string
    {
        return $this->hasInfiniteStart() ? InfinityStyle::SYMBOL : (string) $this->start;
    }

    public function getEndIso(): string
    {
        return $this->hasInfiniteEnd() ? InfinityStyle::SYMBOL : (string) $this->end;
    }

    /**
     * Yields a copy of this interval with given start time.
     */
    public function withStart(?LocalDate $startDate): self
    {
        return self::between($startDate, $this->end);
    }

    /**
     * Yields a copy of this interval with given end time.
     */
    public function withEnd(?LocalDate $endDate): self
    {
        return self::between($this->start, $endDate);
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
        return ($this->hasInfiniteStart() || $this->getFiniteStart()->isBeforeOrEqualTo($date))
            && ($this->hasInfiniteEnd() || $this->getFiniteEnd()->isAfterOrEqualTo($date));
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
            $other->getFiniteEnd()->isBeforeOrEqualTo($this->getFiniteEnd())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Compares the boundaries (start and end) and also the time axis
     * of this and the other interval.
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
        $startB = $other->getFiniteStart();

        return $endA->isBefore($startB->minusDays(1));
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
        $startB = $other->getFiniteStart();

        return $endA->isEqualTo($startB->minusDays(1));
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
        return !($this->hasInfiniteEnd() || $other->hasInfiniteStart())
            && ($this->hasInfiniteStart() || $this->getFiniteStart()->isBefore($other->getFiniteStart()))
            && ($other->hasInfiniteEnd() || $this->getFiniteEnd()->isBeforeOrEqualTo($other->getFiniteEnd()))
            && $this->getFiniteEnd()->isAfterOrEqualTo($other->getFiniteStart());
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
            (null !== $this->end && null === $other->end)
        ) {
            return false;
        }

        if ($this->end && $other->end && !$this->end->isEqualTo($other->end)) {
            return false;
        }

        if ($this->hasInfiniteStart()) {
            return false;
        }

        if ($other->hasInfiniteStart()) {
            return true;
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
        return ($this->hasInfiniteStart() || $other->hasInfiniteEnd() || $this->getFiniteStart()->isBeforeOrEqualTo($other->getFiniteEnd()))
            && ($this->hasInfiniteEnd() || $other->hasInfiniteStart() || $this->getFiniteEnd()->isAfterOrEqualTo($other->getFiniteStart()));
    }

    /**
     * Obtains the intersection of this interval and other one if present.
     */
    public function findIntersection(self $other): ?self
    {
        if ($this->intersects($other)) {
            if ($this->hasInfiniteStart() || $other->hasInfiniteStart()) {
                $start = $this->hasInfiniteStart()
                    ? ($other->hasInfiniteStart() ? null : $other->getFiniteStart())
                    : $this->getFiniteStart();
            } else {
                $start = LocalDate::maxOf($this->getFiniteStart(), $other->getFiniteStart());
            }

            if ($this->hasInfiniteEnd() || $other->hasInfiniteEnd()) {
                $end = $this->hasInfiniteEnd()
                    ? ($other->hasInfiniteEnd() ? null : $other->getFiniteEnd())
                    : $this->getFiniteEnd();
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
            throw new RuntimeException(sprintf('The interval "%s" does not have a finite end.', $this));
        }

        return $this->end;
    }

    public function getFiniteStart(): LocalDate
    {
        if (null === $this->start) {
            throw new RuntimeException(sprintf('The interval "%s" does not have a finite start.', $this));
        }

        return $this->start;
    }
}
