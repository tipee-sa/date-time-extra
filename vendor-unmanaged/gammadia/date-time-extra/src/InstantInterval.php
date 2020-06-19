<?php

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\TimeZone;

class InstantInterval
{
    /**
     * <p>Creates an infinite half-open interval since given start. </p>
     */
    public static function since(Instant $start): self
    {
//        return MomentInterval.since(Moment.from(start));
    }

    /**
     * <p>Creates an infinite open interval until given end. </p>
     */
    public static function until(Instant $end): self
    {
//    Boundary<Moment> past = Boundary.infinitePast();
//        return new MomentInterval(past, Boundary.of(OPEN, end));
    }

    /**
     * <p>Creates an interval surrounding given moment. </p>
     *
     * <p><strong>Alignment: </strong></p>
     * <ul>
     *     <li>If the alignment is {@code 0.0} then the new interval will start at given moment.</li>
     *     <li>If the alignment is {@code 0.5} then the new interval will be centered around given moment.</li>
     *     <li>If the alignment is {@code 1.0} then the new interval will end at given moment.</li>
     * </ul>
     *
     * @param moment      embedded moment at focus of alignment
     * @param duration    machine time duration
     * @param alignment   determines how to align the interval around moment (in range {@code 0.0 <= x <= 1.0})
     * @return  new moment interval
     */
    public static function surrounding(
//        Moment moment,
//        MachineTime
//        double alignment
    ): self
    {

//        if ((Double.compare(alignment, 0.0) < 0) || (Double.compare(alignment, 1.0) > 0)) {
//            throw new IllegalArgumentException("Out of range: " + alignment);
//        }
//
//        Moment start = subtract(moment, duration.multipliedBy(alignment));
//        return MomentInterval.between(start, (alignment == 1.0) ? moment : add(start, duration));
    }

    /**
     * <p>Converts this instance to a local timestamp interval in the system
     * timezone. </p>
     *
     * @return  local timestamp interval in system timezone (leap seconds will
     *          always be lost)
     * @since   2.0
     * @see     Timezone#ofSystem()
     * @see     #toZonalInterval(TZID)
     * @see     #toZonalInterval(String)
     */
    public function toLocalInterval(): LocalDateTimeInterval
    {
//        Boundary<PlainTimestamp> b1;
//        Boundary<PlainTimestamp> b2;
//
//        if (this.getStart().isInfinite()) {
//            b1 = Boundary.infinitePast();
//        } else {
//    PlainTimestamp t1 =
//        this.getStart().getTemporal().toLocalTimestamp();
//            b1 = Boundary.of(this.getStart().getEdge(), t1);
//        }
//
//if (this.getEnd().isInfinite()) {
//    b2 = Boundary.infiniteFuture();
//} else {
//    PlainTimestamp t2 = this.getEnd().getTemporal().toLocalTimestamp();
//            b2 = Boundary.of(this.getEnd().getEdge(), t2);
//        }
//
//return new TimestampInterval(b1, b2);

    }

    /**
     * <p>Converts this instance to a zonal timestamp interval
     * in given timezone. </p>
     *
     * @param tzid    timezone id
     * @return  zonal timestamp interval in given timezone (leap seconds will
     *          always be lost)
     */
    public function toZonalInterval(TimeZone $tzid): ZonedDateTimeInterval
    {
//
//        Boundary<PlainTimestamp> b1;
//        Boundary<PlainTimestamp> b2;
//
//        if (this.getStart().isInfinite()) {
//            b1 = Boundary.infinitePast();
//        } else {
//    PlainTimestamp t1 =
//        this.getStart().getTemporal().toZonalTimestamp(tzid);
//            b1 = Boundary.of(this.getStart().getEdge(), t1);
//        }
//
//if (this.getEnd().isInfinite()) {
//    b2 = Boundary.infiniteFuture();
//} else {
//    PlainTimestamp t2 =
//        this.getEnd().getTemporal().toZonalTimestamp(tzid);
//            b2 = Boundary.of(this.getEnd().getEdge(), t2);
//        }
//
//return new TimestampInterval(b1, b2);

    }


    /**
     * Obtains a random moment within this interval. </p>
     *
     * @return  random moment within this interval
     * @throws  IllegalStateException if this interval is infinite or empty or if there is no canonical form
     * @see     #toCanonical()
     * @since   5.0
     */
    public function random(): Instant
    {
//
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
     * <p>Interpretes given ISO-conforming text as interval. </p>
     *
     * <p>All styles are supported, namely calendar dates, ordinal dates
     * and week dates, either in basic or in extended format. Mixed date
     * styles for start and end are not allowed however. Furthermore, one
     * of start or end can also be represented by a period string. If not
     * then the end component may exist in an abbreviated form as
     * documented in ISO-8601-paper leaving out higher-order elements
     * like the calendar year (which will be overtaken from the start
     * component instead). In latter case, the timezone offset of the
     * end component is optional, too. Infinity symbols are understood
     * as extension although strictly spoken ISO-8601 does not know or
     * specify infinite intervals. Examples for supported formats: </p>
     *
     * <pre>
     *  System.out.println(
     *      MomentInterval.parseISO(
     *          &quot;2012-01-01T14:15Z/2014-06-20T16:00Z&quot;));
     *  // output: [2012-01-01T14:15:00Z/2014-06-20T16:00:00Z)
     *
     *  System.out.println(
     *      MomentInterval.parseISO(
     *          &quot;2012-01-01T14:15Z/08-11T16:00+00:01&quot;));
     *  // output: [2012-01-01T14:15:00Z/2012-08-11T15:59:00Z)
     *
     *  System.out.println(
     *      MomentInterval.parseISO(
     *          &quot;2012-01-01T14:15Z/16:00&quot;));
     *  // output: [2012-01-01T14:15:00Z/2012-01-01T16:00:00Z)
     *
     *  System.out.println(
     *      MomentInterval.parseISO(
     *          &quot;2012-01-01T14:15Z/P2DT1H45M&quot;));
     *  // output: [2012-01-01T14:15:00Z/2012-01-03T16:00:00Z)
     *
     *  System.out.println(
     *      MomentInterval.parseISO(
     *          &quot;2015-01-01T08:45Z/-&quot;));
     *  // output: [2015-01-01T08:45:00Z/+&#x221E;)
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
//		int n = Math.min(text.length(), 117);
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
//            sameFormat = ((firstDate == secondDate) && (literals == literals2) && hasSecondOffset(text, n));
//        }
//
//        // prepare component parsers
//        ChronoFormatter<Moment> startFormat = (
//extended ? Iso8601Format.EXTENDED_DATE_TIME_OFFSET : Iso8601Format.BASIC_DATE_TIME_OFFSET);
//        ChronoFormatter<Moment> endFormat = (sameFormat ? startFormat : null); // null means reduced iso format
//
//        // create interval
//        Parser parser = new Parser(startFormat, endFormat, extended, weekStyle, ordinalStyle, timeLength, hasT);
//        return parser.parse(text);
//
    }

    /**
     * <p>Yields the length of this interval on the POSIX-scale. </p>
     *
     * @return  machine time duration on POSIX-scale
     * @throws  UnsupportedOperationException if this interval is infinite
     * @since   2.0
     * @see     #getRealDuration()
     */
    public function getDuration(): Duration
    {
//        Moment tsp = this.getTemporalOfOpenEnd();
//        boolean max = (tsp == null);
//
//        if (max) { // max reached
//            tsp = this.getEnd().getTemporal();
//        }
//
//MachineTime<TimeUnit> result =
//    MachineTime.ON_POSIX_SCALE.between(
//        this.getTemporalOfClosedStart(),
//        tsp);
//
//        if (max) {
//            return result.plus(1, TimeUnit.NANOSECONDS);
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
//
//        if (amount == 0) {
//            return this;
//        }
//
//Boundary<Moment> s;
//        Boundary<Moment> e;
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
//        return new MomentInterval(s, e);
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
//
//        MomentInterval interval = this.toCanonical();
//        Moment start = interval.getStartAsMoment();
//        Moment end = interval.getEndAsMoment();
//
//        if ((start == null) || (end == null)) {
//            throw new IllegalStateException("Streaming is not supported for infinite intervals.");
//        }
//
//        return MomentInterval.stream(duration, start, end);
    }

    // todo @see format(...) methods from Time4j classes
    public function toString(): string
    {

    }
}
