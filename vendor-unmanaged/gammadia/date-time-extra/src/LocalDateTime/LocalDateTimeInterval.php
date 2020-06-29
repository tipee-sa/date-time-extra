<?php

namespace Gammadia\DateTimeExtra\LocalDateTime;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;
use Gammadia\DateTimeExtra\Api\IsoInterval;
use Gammadia\DateTimeExtra\Instant\InstantInterval;
use Gammadia\DateTimeExtra\Share\InfinityStyle;
use Gammadia\DateTimeExtra\Share\IntervalParseException;
use Gammadia\DateTimeExtra\ZonedDateTime\ZonedDateTimeInterval;
use Symfony\Component\String\UnicodeString;
use Traversable;
use Webmozart\Assert\Assert;

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
            throw new \InvalidArgumentException("Start after end: $start / $end");
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Creates a finite half-open interval between given time points.
     *
     * @param LocalDateTime $start the local datetime of lower boundary (inclusive)
     * @param LocalDateTime $end the local datetime of upper boundary (exclusive)
     * @return  self LocalDateTimeInterval interval
     */
    public static function between(?LocalDateTime $start, ?LocalDateTime $end): self
    {
        return new self($start, $end);
    }

    /**
     * <p>Creates an infinite half-open interval since given start. </p>
     *
     * @param LocalDateTime $start the local datetime of lower boundary (inclusive)
     * @return self new local datetime interval
     */
    public static function since(LocalDateTime $start): self
    {
        return new self($start, null);
    }

    /**
     * <p>Creates an infinite open interval until given end timestamp. </p>
     *
     * @param LocalDateTime $end the local datetime of upper boundary (exclusive)
     * @return self new timestamp interval
     */
    public static function until(LocalDateTime $end): self
    {
        return new self(null, $end);
    }

    public function getStart(): ?LocalDateTime
    {
        return $this->start;
    }

    public function getEnd(): ?LocalDateTime
    {
        return $this->end;
    }

    public function withStart(LocalDateTime $t): self
    {
        return self::between($t, $this->end);
    }

    public function withEnd(LocalDateTime $t): self
    {
        return self::between($this->start, $t);
    }

    /**
     * @return string the canonical form of this interval in given ISO-8601 style.
     */
    public function toIsoString(): string
    {
        $output = '';
        if ($this->hasInfiniteStart()) {
            $output .= InfinityStyle::SYMBOL;
        } else {
            $output .= $this->start;
        }

        $output .= '/';

        if ($this->hasInfiniteEnd()) {
            $output .= InfinityStyle::SYMBOL;
        } else {
            $output .= $this->end;
        }

        return $output;
    }

    /**
     * Combines this local timestamp interval with the timezone offset
     * UTC+00:00 to a global UTC-interval.
     *
     * @return InstantInterval global timestamp interval interpreted at offset UTC+00:00
     */
    public function atUTC(): InstantInterval
    {
        return InstantInterval::between(
            Converter::toInstant($this->start, TimeZoneOffset::utc()),
            Converter::toInstant($this->end, TimeZoneOffset::utc())
        );
    }

    /**
     * Combines this local timestamp interval with given timezone
     * to a global UTC-interval.
     *
     * @param TimeZoneRegion $timezoneId timezone id
     * @return ZonedDateTimeInterval zoned datetime interval interpreted in given timezone
     */
    public function atTimeZone(TimeZoneRegion $timezoneId): ZonedDateTimeInterval
    {
        return ZonedDateTimeInterval::between(
            $this->start->atTimeZone($timezoneId),
            $this->end->atTimeZone($timezoneId)
        );
    }

    /**
     * <p>Interpreters a given text as interval. </p>
     *
     * @param string $text text to be parsed
     */
    public static function parse(string $text): self
    {
        Assert::notEmpty($text);

        [$startStr, $endStr] = explode('/', trim($text), 2);

        $startStr = new UnicodeString($startStr);
        $endStr = new UnicodeString($endStr);

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
            $ldt2 = LocalDateTime::parse($endStr);
            $ldt1 = $startStr->indexOf('T')
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
            $ldt2 = $endStr->indexOf('T')
                ? $ldt1->plusDuration(Duration::parse($endStr))
                : $ldt1->plusPeriod(Period::parse($endStr));
        } else {
            $ldt2 = LocalDateTime::parse($endStr);
        }

        return self::between($ldt1, $ldt2);
    }

    /**
     * <p>Moves this interval along the POSIX-axis by given time units. </p>
     *
     * @param Duration|Period $durationOrPeriod
     * @return self moved copy of this interval
     */
    public function move($durationOrPeriod): self
    {
        if ($durationOrPeriod instanceof Duration) {
            return new self(
                $this->start->plusDuration($durationOrPeriod),
                $this->end->plusDuration($durationOrPeriod)
            );
        }

        if ($durationOrPeriod instanceof Period) {
            return new self(
                $this->start->plusPeriod($durationOrPeriod),
                $this->end->plusPeriod($durationOrPeriod)
            );
        }

        throw new \RuntimeException('The given value must be either Duration or Period.');
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

        return $this->atUTC()->getDuration();
    }

    /**
     * <p>Obtains a stream iterating over every moment which is the result of addition of given duration
     * to start until the end of this interval is reached. </p>
     * @return Traversable
     */
    public function stream(?Period $period, ?Duration $duration): Traversable
    {
        if (!$duration && !$period) {
            throw new \RuntimeException('Duration and Period can not be both null.');
        }

        if (!$this->isFinite()) {
            throw new \RuntimeException("Streaming is not supported for infinite intervals.");
        }

        for ($start = $this->start; $start->isBefore($this->end);) {
            yield $start;

            if ($period) {
                $start = $start->plusPeriod($period);
            }

            if ($duration) {
                $start = $start->plusDuration($duration);
            }
        }
    }

    public function isEmpty(): bool
    {
        if ($this->isFinite()) {
            return $this->start->compareTo($this->end) === 0;
        }

        return false;
    }

    public function isBefore(LocalDateTime $t): bool
    {
        if ($this->hasInfiniteEnd()) {
            return false;
        }

        return $this->end->isBeforeOrEqualTo($t);
    }

    public function isBeforeInterval(self $other): bool
    {
        if ($other->hasInfiniteStart() || $this->hasInfiniteEnd()) {
            return false;
        }

        $endA = $this->end;
        $startB = $other->getStart();

        if ($startB === null) {
            return true;
        }

        return $endA->isBeforeOrEqualTo($startB);
    }

    public function isAfter(LocalDateTime $t): bool
    {
        if ($this->hasInfiniteStart()) {
            return false;
        }

        return $this->start->isAfter($t);
    }

    public function isAfterInterval(self $other): bool
    {
        return $other->isBeforeInterval($this);
    }

    public function contains(LocalDateTime $temporal): bool
    {
        if ($this->hasInfiniteStart()) {
            $startCondition = true;
        } else {
            $startCondition = !$this->start->isAfter($temporal);
        }

        if (!$startCondition) {
            return false; // short-cut
        }

        if ($this->hasInfiniteEnd()) {
            $endCondition = true;
        } else {
            $endCondition = $this->end->isAfter($temporal);
        }

        return $endCondition;
    }

    public function containsInterval(self $other): bool
    {
        if (!$other->isFinite()) {
            return false;
        }
        
        if ($this->hasInfiniteStart() && $other->end->isBeforeOrEqualTo($this->end)) {
            return true;
        }

        if ($this->hasInfiniteStart() && !$other->end->isBeforeOrEqualTo($this->end)) {
            return false;
        }

        if ($this->hasInfiniteEnd() && $other->start->isAfterOrEqualTo($this->start)) {
            return true;
        }

        if ($this->hasInfiniteEnd() && !$other->start->isAfterOrEqualTo($this->start)) {
            return false;
        }

        if ($other->start->isAfterOrEqualTo($this->start) && $other->end->isBeforeOrEqualTo($this->end)) {
            return true;
        }

        return false;
    }

    public function collapse(): IsoInterval
    {
        // TODO: Implement collapse() method.
    }

    public function equals(self $other): bool
    {
        // TODO: Implement equals() method.
    }

    public function toString(): string
    {
        // TODO: Implement toString() method.
    }

    public function precedes(self $other): bool
    {
        if ($other->hasInfiniteStart() || $this->hasInfiniteEnd()) {
            return false;
        }

        $endA = $this->end;
        $startB = $other->getStart();

        if ($startB === null) {
            return true;
        }

        return $endA->isBefore($startB);
    }

    public function precededBy(self $other): bool
    {
        return $other->precedes($this);
    }

    public function meets(self $other): bool
    {
        if ($other->hasInfiniteStart() || $this->hasInfiniteEnd()) {
            return false;
        }

        return $this->end->isEqualTo($other->start);
    }

    public function metBy(self $other): bool
    {
        return $other->meets($this);
    }

    public function overlaps(self $other): bool
    {
        // TODO: Implement overlaps() method.
    }

    public function overlap(self $other): self
    {
        // TODO: Implement overlaps() method.
    }

    public function overlappedBy(self $other): bool
    {
        // TODO: Implement overlappedBy() method.
    }

    public function finishes(self $other): bool
    {
        // TODO: Implement finishes() method.
    }

    public function finishedBy(self $other): bool
    {
        // TODO: Implement finishedBy() method.
    }

    public function starts(self $other): bool
    {
        // TODO: Implement starts() method.
    }

    public function startedBy(self $other): bool
    {
        // TODO: Implement startedBy() method.
    }

    public function encloses(self $other): bool
    {
        // TODO: Implement encloses() method.
    }

    public function enclosedBy(self $other): bool
    {
        // TODO: Implement enclosedBy() method.
    }

    public function intersects(self $other): bool
    {
        // TODO: Implement intersects() method.
    }

    public function findIntersection(self $other): IsoInterval
    {
        // TODO: Implement findIntersection() method.
    }

    public function abuts(self $other): bool
    {
        // TODO: Implement abuts() method.
    }

    public function isFinite(): bool
    {
        return !($this->hasInfiniteStart() || $this->hasInfiniteEnd());
    }

    public function hasInfiniteStart(): bool
    {
        return null === $this->start;
    }

    public function hasInfiniteEnd(): bool
    {
        return null === $this->end;
    }
}
