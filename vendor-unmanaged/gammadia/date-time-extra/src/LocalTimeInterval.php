<?php

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;

class LocalTimeInterval
{
    /**
     * @var LocalTime
     */
    protected $start;

    /**
     * @var LocalTime
     */
    protected $end;

    public function __construct(LocalTime $start, LocalTime $end)
    {
        if ($start->isAfter($end)) {
            throw new \InvalidArgumentException("Start after end: $start / $end");
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * <p>Creates a finite half-open interval between given start time and
     * midnight at end of day (exclusive). </p>
     *
     * @param start       time of lower boundary (inclusive)
     * @return  new time interval
     * @see     #since(PlainTime)
     * @since   4.11
     */
    public static function since(LocalTime $start): self
    {

//        return ClockInterval.since(PlainTime.from(start));
    }

    /**
     * <p>Creates a finite half-open interval between midnight at start of day
     * and given end time. </p>
     *
     * @param end     time of upper boundary (exclusive)
     * @return  new time interval
     * @since   2.0
     */
    public static function until(LocalTime $end): self
    {
//        return between(PlainTime.midnightAtStartOfDay(), end);
    }

    /**
     * <p>Yields the length of this interval. </p>
     *
     * @return  duration in hours, minutes, seconds and nanoseconds
     * @since   2.0
     */
    public function getDuration(): Duration
    {

//        PlainTime t1 = this.getTemporalOfClosedStart();
//        PlainTime t2 = this.getEnd().getTemporal();
//
//        if (this.getEnd().isClosed()) {
//            if (t2.getHour() == 24) {
//                if (t1.equals(PlainTime.midnightAtStartOfDay())) {
//                    return Duration.of(24, HOURS).plus(1, NANOS);
//                } else {
//        t1 = t1.minus(1, NANOS);
//    }
//    } else {
//        t2 = t2.plus(1, NANOS);
//    }
//}
//
//return Duration.inClockUnits().between(t1, t2);
    }


    /**
     * <p>Moves this interval along the time axis by given units. </p>
     *
     * @param amount  amount of units
     * @param unit    time unit for moving
     * @return  moved copy of this interval
     */
    public function move(
//       long $amount,
        //ClockUnit unit
    ): self
    {
//
//        if (amount == 0) {
//            return this;
//        }
//
//        Boundary<PlainTime> s;
//        Boundary<PlainTime> e;
//
//        if (this.getStart().isInfinite()) {
//            s = Boundary.infinitePast();
//        } else {
//            s =
//                Boundary.of(
//                    this.getStart().getEdge(),
//                    this.getStart().getTemporal().plus(amount, unit));
//        }
//
//        if (this.getEnd().isInfinite()) {
//            e = Boundary.infiniteFuture();
//        } else {
//            e =
//                Boundary.of(
//                    this.getEnd().getEdge(),
//                    this.getEnd().getTemporal().plus(amount, unit));
//        }
//
//        return new ClockInterval(s, e);
//
//    }
    }

    /**
     * <p>Obtains a stream iterating over every clock time which is the result of addition of given duration
     * to start until the end of this interval is reached. </p>
     *
     * <p>The stream size is limited to {@code Integer.MAX_VALUE - 1} else an {@code ArithmeticException}
     * will be thrown. </p>
     *
     * @param duration    duration which has to be added to the start multiple times
     * @return  stream consisting of distinct clock times which are the result of adding the duration to the start
     * @throws  IllegalStateException if this interval has no canonical form
     * @throws  IllegalArgumentException if the duration is not positive
     * @see     #toCanonical()
     * @see     #stream(Duration, PlainTime, PlainTime)
     * @since   4.18
     */
    public function stream(
        //Duration<ClockUnit> duration
    ): \Iterator // @todo iterate()
    {
//
//        ClockInterval interval = this.toCanonical();
//        return ClockInterval.stream(duration, interval.getStartAsClockTime(), interval.getEndAsClockTime());

    }

    /**
     * Obtains a random time within this interval. </p>
     *
     * @return  random time within this interval
     * @throws  IllegalStateException if this interval is empty or if there is no canonical form
     * @see     #toCanonical()
     * @since   5.0
     */
    public function random(): LocalTime
    {

//        ClockInterval interval = this.toCanonical();
//
//        if (interval.isEmpty()) {
//            throw new IllegalStateException("Cannot get random time in an empty interval: " + this);
//        } else {
//    long s = interval.getStartAsClockTime().get(PlainTime.NANO_OF_DAY).longValue();
//            long e = interval.getEndAsClockTime().get(PlainTime.NANO_OF_DAY).longValue();
//            long randomNum = ThreadLocalRandom.current().nextLong(s, e);
//            return PlainTime.midnightAtStartOfDay().plus(randomNum, ClockUnit.NANOS);
//        }
//
    }

    /**
     * <p>Prints the canonical form of this interval in given basic ISO-8601 style. </p>
     *
     * @param decimalStyle    iso-compatible decimal style
     * @param precision       controls the precision of output format with constant length
     * @return  String
     * @throws  IllegalStateException if there is no canonical form (for example for [00:00/24:00])
     * @see     #toCanonical()
     * @since   4.18
     */
    public function formatBasicISO
    (
//    IsoDecimalStyle decimalStyle,
//        ClockUnit precision
    ): string
    {

//    ClockInterval interval = this.toCanonical();
//        StringBuilder buffer = new StringBuilder();
//        ChronoPrinter<PlainTime> printer = Iso8601Format.ofBasicTime(decimalStyle, precision);
//        printer.print(interval.getStartAsClockTime(), buffer);
//        buffer.append('/');
//        printer.print(interval.getEndAsClockTime(), buffer);
//        return buffer.toString();
//
    }

    /**
     * <p>Interpretes given ISO-conforming text as interval. </p>
     *
     * <p>Examples for supported formats: </p>
     *
     * <ul>
     *     <li>09:45/PT5H</li>
     *     <li>PT5H/14:45</li>
     *     <li>0945/PT5H</li>
     *     <li>PT5H/1445</li>
     *     <li>PT01:55:30/14:15:30</li>
     *     <li>04:01:30.123/24:00:00.000</li>
     *     <li>04:01:30,123/24:00:00,000</li>
     * </ul>
     *
     * @param   text        text to be parsed
     * @return  parsed interval
     * @throws  IndexOutOfBoundsException if given text is empty
     * @throws  ParseException if the text is not parseable
     * @since   2.1
     * @see     BracketPolicy#SHOW_NEVER
     */

    public static function parseISO(string $text): self
    {

//        if (text.isEmpty()) {
//            throw new IndexOutOfBoundsException("Empty text.");
//        }
//
//ChronoParser<PlainTime> parser = (
//(text.indexOf(':') == -1) ? Iso8601Format.BASIC_WALL_TIME : Iso8601Format.EXTENDED_WALL_TIME);
//        ParseLog plog = new ParseLog();
//
//        ClockInterval result =
//    new IntervalParser<>(
//        ClockIntervalFactory.INSTANCE,
//                parser,
//                parser,
//                BracketPolicy.SHOW_NEVER,
//                '/'
//            ).parse(text, plog, parser.getAttributes());
//
//        if ((result == null) || plog.isError()) {
//            throw new ParseException(plog.getErrorMessage(), plog.getErrorIndex());
//        } else if (plog.getPosition() < text.length()) {
//            throw new ParseException("Trailing characters found: " + text, plog.getPosition());
//        } else {
//            return result;
//        }

    }

    public function toString(): string
    {
        // todo @see format(...) methods from Time4j classes
    }

}
