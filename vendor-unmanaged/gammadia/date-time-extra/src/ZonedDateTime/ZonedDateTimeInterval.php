<?php

namespace Gammadia\DateTimeExtra\ZonedDateTime;

use Brick\DateTime\Duration;
use Brick\DateTime\ZonedDateTime;
use Gammadia\DateTimeExtra\Instant\InstantInterval;

//todo voir InstantInterval.php
class ZonedDateTimeInterval
{
    /**
     * @var ZonedDateTime
     */
    private $start;

    /**
     * @var ZonedDateTime
     */
    private $end;

    private function __construct(ZonedDateTime $start, ZonedDateTime $end)
    {
        if ($start->isAfter($end)) {
            throw new \InvalidArgumentException("Start after end: $start / $end");
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Creates a finite half-open interval between given time points.
     *
     * @param  ZonedDateTime $start   the zoned datetime of lower boundary (inclusive)
     * @param  ZonedDateTime $end     the zoned datetime of upper boundary (exclusive)
     * @return  self ZonedDateTimeInterval interval
     */
    public static function between(ZonedDateTime $start, ZonedDateTime $end): self
    {
        return new self($start, $end);
    }

    public function atUTC(): InstantInterval
    {
        return InstantInterval::between($this->start->getInstant(), $this->end->getInstant());
    }


    /**
     * <p>Yields the length of this interval in given units and applies
     * a timezone offset correction . </p>
     *
     * @return  duration in given units including a zonal correction
     */
    public function getDuration(): Duration
    {
        return Duration::between($this->start->getInstant(), $this->end->getInstant());
    }


}
