<?php

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;

class LocalDateTimeInterval
{
    /**
     * @var LocalDateTime
     */
    private $start;

    /**
     * @var LocalDateTime
     */
    private $end;

    private function __construct(LocalDateTime $start, LocalDateTime $end)
    {
        if ($start->isAfter($end)) {
            throw new \InvalidArgumentException("Start after end: $start / $end");
        }

        $this->start = $start;
        $this->end = $end;
    }

    public static function between(LocalDateTime $start, LocalDateTime $end): self
    {
        return new self($start, $end);
    }

    /**
     * <p>Creates an infinite half-open interval since given start. </p>
     */
    public static function since(LocalDateTime $start): self
    {
//        return MomentInterval.since(Moment.from(start));
    }

    /**
     * <p>Creates an infinite open interval until given end. </p>
     */
    public static function until(LocalDateTime $end): self
    {

//    Boundary<Moment> past = Boundary.infinitePast();
//        return new MomentInterval(past, Boundary.of(OPEN, end));
    }

    /**
     * <p>Combines this local timestamp interval with the timezone offset
     * UTC+00:00 to a global UTC-interval. </p>
     *
     * @return  global timestamp interval interpreted at offset UTC+00:00
     */
    public function atUTC(): InstantInterval
    {
//        return this.at(ZonalOffset.UTC);
    }


    /**
     * <p>Combines this local timestamp interval with given timezone offset
     * to a global UTC-interval. </p>
     *
     * @param offset  timezone offset
     * @return  global timestamp interval interpreted at given offset
     * @since   2.0
     * @see     #atUTC()
     * @see     #inTimezone(TZID)
     */
    public function at(TimeZoneOffset $offset): InstantInterval
    {
//
//        Boundary<Moment> b1;
//        Boundary<Moment> b2;
//
//        if (this.getStart().isInfinite()) {
//            b1 = Boundary.infinitePast();
//        } else {
//    Moment m1 = this.getStart().getTemporal().at(offset);
//            b1 = Boundary.of(this.getStart().getEdge(), m1);
//        }
//
//if (this.getEnd().isInfinite()) {
//    b2 = Boundary.infiniteFuture();
//} else {
//    Moment m2 = this.getEnd().getTemporal().at(offset);
//            b2 = Boundary.of(this.getEnd().getEdge(), m2);
//        }
//
//return new MomentInterval(b1, b2);

    }

    /**
     * <p>Combines this local timestamp interval with given timezone
     * to a global UTC-interval. </p>
     *
     * @param tzid        timezone id
     * @return  global timestamp interval interpreted in given timezone
     * @throws  IllegalArgumentException if given timezone cannot be loaded
     * @since   2.0
     * @see     Timezone#of(TZID)
     * @see     #inStdTimezone()
     * @see     GapResolver#NEXT_VALID_TIME
     * @see     OverlapResolver#EARLIER_OFFSET
     */
    public function inTimezone(TimeZone $tzid): ZonedDateTimeInterval
    {
//        return this.in(Timezone.of(tzid).with(GapResolver.NEXT_VALID_TIME.and(OverlapResolver.EARLIER_OFFSET)));
    }


    /**
     * <p>Interpretes given ISO-conforming text as interval. </p>
     *
     * <p>All styles are supported, namely calendar dates, ordinal dates
     * and week dates, either in basic or in extended format. Mixed date
     * styles for start and end are not allowed however. Furthermore, one
     * of start or end can also be represented by a period string. If not
     * then the end component may exist in an abbreviated form as
     * documented in ISO-8601-paper leaving out higher-order elements
     * like the calendar year (which will be overtaken from the start
     * component instead). Infinity symbols are understood as extension
     * although strictly spoken ISO-8601 does not know or specify infinite
     * intervals. Examples for supported formats: </p>
     *
     * <pre>
     *  System.out.println(
     *      TimestampInterval.parseISO(
     *          &quot;2012-01-01T14:15/2014-06-20T16:00&quot;));
     *  // output: [2012-01-01T14:15/2014-06-20T16:00)
     *
     *  System.out.println(
     *      TimestampInterval.parseISO(
     *          &quot;2012-01-01T14:15/08-11T16:00&quot;));
     *  // output: [2012-01-01T14:15/2012-08-11T16:00)
     *
     *  System.out.println(
     *      TimestampInterval.parseISO(
     *          &quot;2012-01-01T14:15/16:00&quot;));
     *  // output: [2012-01-01T14:15/2012-01-01T16:00)
     *
     *  System.out.println(
     *      TimestampInterval.parseISO(
     *          &quot;2012-01-01T14:15/P2DT1H45M&quot;));
     *  // output: [2012-01-01T14:15/2012-01-03T16:00)
     *
     *  System.out.println(
     *      TimestampInterval.parseISO(
     *          &quot;2015-01-01T08:45/-&quot;));
     *  // output: [2015-01-01T08:45:00/+&#x221E;)
     * </pre>
     *
     * <p>This method dynamically creates an appropriate interval format for reduced forms.
     * If performance is more important then a static fixed formatter might be considered. </p>
     *
     * @param   text        text to be parsed
     * @return  parsed interval
     * @throws  IndexOutOfBoundsException if given text is empty
     * @throws  ParseException if the text is not parseable
     * @since   2.0
     * @see     BracketPolicy#SHOW_NEVER
     */
    public static function parseISO(string $text): self
    {
//
//        if (text.isEmpty()) {
//            throw new IndexOutOfBoundsException("Empty text.");
//        }
//
//// prescan for format analysis
//int start = 0;
//        int n = Math.min(text.length(), 107);
//        boolean sameFormat = true;
//        int firstDate = 1; // loop starts one index position later
//        int secondDate = 0;
//        int timeLength = 0;
//        boolean startsWithHyphen = (text.charAt(0) == '-');
//
//        if ((text.charAt(0) == 'P') || startsWithHyphen) {
//            for (int i = 1; i < n; i++) {
//                if (text.charAt(i) == '/') {
//                    if (i + 1 == n) {
//                        throw new ParseException("Missing end component.", n);
//                    } else if (startsWithHyphen) {
//                        if ((text.charAt(1) == '\u221E') || (i == 1)) {
//                            start = i + 1;
//                        }
//                    } else {
//                        start = i + 1;
//                    }
//                    break;
//                }
//            }
//        }
//
//        int literals = 0;
//        int literals2 = 0;
//        boolean ordinalStyle = false;
//        boolean weekStyle = false;
//        boolean weekStyle2 = false;
//        boolean secondComponent = false;
//        int slash = -1;
//
//        for (int i = start + 1; i < n; i++) {
//    char c = text.charAt(i);
//            if (secondComponent) {
//                if (
//                    (c == 'P')
//                    || ((c == '-') && (i == n - 1))
//                    || ((c == '+') && (i == n - 2) && (text.charAt(i + 1) == '\u221E'))
//                ) {
//                    secondComponent = false;
//                    break;
//                } else if ((c == 'T') || (timeLength > 0)) {
//                    timeLength++;
//                } else {
//                    if (c == 'W') {
//                        weekStyle2 = true;
//                    } else if ((c == '-') && (i > slash + 1)) {
//                        literals2++;
//                    }
//                    secondDate++;
//                }
//            } else if (c == '/') {
//                if (slash == -1) {
//                    slash = i;
//                    secondComponent = true;
//                    timeLength = 0;
//                } else {
//                    throw new ParseException("Interval with two slashes found: " + text, i);
//                }
//            } else if ((c == 'T') || (timeLength > 0)) {
//                timeLength++;
//            } else if (c == '-') {
//                firstDate++;
//                literals++;
//            } else if (c == 'W') {
//                firstDate++;
//                weekStyle = true;
//            } else {
//                firstDate++;
//            }
//        }
//
//        if (secondComponent && (weekStyle != weekStyle2)) {
//            throw new ParseException("Mixed date styles not allowed.", n);
//        }
//
//        char c = text.charAt(start);
//        int componentLength = firstDate - 4;
//
//        if ((c == '+') || (c == '-')) {
//            componentLength -= 2;
//        }
//
//        if (!weekStyle) {
//            ordinalStyle = ((literals == 1) || ((literals == 0) && (componentLength == 3)));
//        }
//
//        boolean extended = (literals > 0);
//        boolean hasT = true;
//
//        if (secondComponent) {
//            if (timeLength == 0) { // no T in end component => no date part
//                hasT = false;
//                timeLength = secondDate;
//                secondDate = 0;
//            }
//            sameFormat = ((firstDate == secondDate) && (literals == literals2));
//        }
//
//        // prepare component parsers
//        ChronoFormatter<PlainTimestamp> startFormat = (
//extended ? Iso8601Format.EXTENDED_DATE_TIME : Iso8601Format.BASIC_DATE_TIME);
//        ChronoFormatter<PlainTimestamp> endFormat = (sameFormat ? startFormat : null); // null means reduced iso format
//
//        // create interval
//        Parser parser = new Parser(startFormat, endFormat, extended, weekStyle, ordinalStyle, timeLength, hasT);
//        return parser.parse(text);

    }

    /**
     * Obtains a random moment within this interval. </p>
     */
    public function random(): LocalDateTime
    {

//        MomentInterval interval = this.toCanonical();
//
//        if (interval.isFinite() && !interval.isEmpty()) {
//            Moment m1 = interval.getStartAsMoment();
//            Moment m2 = interval.getEndAsMoment();
//            double factor = MRD;
//            double d1 = m1.getPosixTime() + m1.getNanosecond() / factor;
//            double d2 = m2.getPosixTime() + m2.getNanosecond() / factor;
//            double randomNum = ThreadLocalRandom.current().nextDouble(d1, d2);
//            long posix = (long) Math.floor(randomNum);
//            int fraction = (int) (MRD * (randomNum - posix));
//            Moment random = Moment.of(posix, fraction, TimeScale.POSIX);
//            if (random.isBefore(m1)) {
//                random = m1;
//            } else if (random.isAfterOrEqual(m2)) {
//    random = m2.minus(1, TimeUnit.NANOSECONDS);
//}
//return random;
//} else {
//    throw new IllegalStateException("Cannot get random moment in an empty or infinite interval: " + this);
//}

    }

    /**
     * <p>Yields the length of this interval in given units and applies
     * a timezone offset correction . </p>
     *
     * @param   tz      timezone
     * @param   units   time units to be used in calculation
     * @return  duration in given units including a zonal correction
     * @throws  UnsupportedOperationException if this interval is infinite
     * @since   2.0
     */
    public function getDuration(
        Timezone $tz
//        IsoUnit... units
    ): Duration {
//
//        PlainTimestamp tsp = this.getTemporalOfOpenEnd();
//        boolean max = (tsp == null);
//
//        if (max) { // max reached
//            tsp = this.getEnd().getTemporal();
//        }
//
//Duration<IsoUnit> result =
//    Duration.in(tz, units).between(
//        this.getTemporalOfClosedStart(),
//        tsp);
//
//        if (max) {
//            for (IsoUnit unit : units) {
//                if (unit.equals(ClockUnit.NANOS)) {
//                    return result.plus(1, unit);
//                }
//            }
//        }
//
//        return result;

    }


    /**
     * <p>Moves this interval along the POSIX-axis by given time units. </p>
     *
     * @param   amount  amount of units
     * @param   unit    time unit for moving
     * @return  moved copy of this interval
     */
    public function move(
        int $amount
//        TimeUnit unit todo? Make a constant out of it?
    ): self {

//        if (amount == 0) {
//            return this;
//        }
//
//        Boundary<PlainTimestamp> s;
//        Boundary<PlainTimestamp> e;
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
//        return new TimestampInterval(s, e);
    }

    /**
     * <p>Obtains a stream iterating over every moment which is the result of addition of given duration
     * to start until the end of this interval is reached. </p>
     *
     * <p>The stream size is limited to {@code Integer.MAX_VALUE - 1} else an {@code ArithmeticException}
     * will be thrown. </p>
     *
     * @param   duration    duration which has to be added to the start multiple times
     * @return  stream consisting of distinct moments which are the result of adding the duration to the start
     * @throws  IllegalStateException if this interval is infinite or if there is no canonical form
     * @throws  IllegalArgumentException if the duration is not positive
     * @see     #toCanonical()
     * @see     #stream(MachineTime, Moment, Moment)
     * @since   4.35
     */
    /* public Stream<Moment> stream(MachineTime<?><!-- duration) {*/
    public function stream(Duration $duration) {//todo rename: iterate? Add a slice() and split() methode. See Gammadia\Moment\Period
//        TimestampInterval interval = this.toCanonical();
//        PlainTimestamp start = interval.getStartAsTimestamp();
//        PlainTimestamp end = interval.getEndAsTimestamp();
//
//        if ((start == null) || (end == null)) {
//            throw new IllegalStateException("Streaming is not supported for infinite intervals.");
//        }
//
//        return TimestampInterval.stream(duration, start, end);
    }

    // todo @see format(...) methods from Time4j classes
    public function toString(): string
    {

    }
}
