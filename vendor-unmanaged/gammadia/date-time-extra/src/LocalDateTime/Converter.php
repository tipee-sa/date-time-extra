<?php

namespace Gammadia\DateTimeExtra\LocalDateTime;

use Brick\DateTime\Instant;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\TimeZoneOffset;

class Converter
{
    /**
     * Combines a local datetime with the given timezone offset to create an Instant.
     *
     * @param LocalDateTime $localDateTime
     * @param TimeZoneOffset $offset timezone offset
     * @return Instant global instant based on the given local datetime interpreted at given offset
     * @see PlainTimestamp::at() from time4j
     */
    public static function toInstant(LocalDateTime $localDateTime, TimeZoneOffset $offset): Instant
    {
        $localSeconds
            = ($localDateTime->getDate()->toEpochDay()) * LocalTime::SECONDS_PER_DAY
            + $localDateTime->getTime()->toSecondOfDay()
            - $offset->getTotalSeconds();

        return Instant::of($localSeconds, $localDateTime->getTime()->getNano());
    }
}
