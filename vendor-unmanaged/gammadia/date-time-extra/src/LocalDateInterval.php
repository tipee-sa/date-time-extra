<?php

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDate;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZone;

class LocalDateInterval
{
    /**
     * @var LocalDate
     */
    private $start;

    /**
     * @var LocalDate
     */
    private $end;

    private function __construct(LocalDate $start, LocalDate $end)
    {
        if ($start->isAfter($end)) {
            throw new \InvalidArgumentException("Start after end: $start / $end");
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * <p>Creates an infinite interval since given start date. </p>
     *
     * @param start   date of lower boundary (inclusive)
     * @return  new date interval
     * @see     #since(PlainDate)
     * @since   4.11
     */
    public static function since(LocalDate $start): self
    {

//        return DateInterval.since(PlainDate.from(start));
    }

    /**
     * <p>Creates an infinite interval until given end date. </p>
     *
     * @param end     date of upper boundary (inclusive)
     * @return  new date interval
     * @since   2.0
     */
    public static function until(LocalDate $end): self
    {
//    Boundary<PlainDate> past = Boundary.infinitePast();
//        return new DateInterval(past, Boundary.of(CLOSED, end));

    }

    /**
     * <p>Creates a closed interval including only given date. </p>
     *
     * @param date    single contained date
     * @return  new date interval
     * @since   2.0
     */
    public static function atomic(LocalDate $date): self
    {

//        return between(date, date);
    }


    /**
     * <p>Obtains the current calendar week based on given clock, time zone and first day of week. </p>
     *
     * <p>A localized first day of week can be obtained by the expression
     * {@code Weekmodel.of(Locale.getDefault()).getFirstDayOfWeek()}. The
     * next week can be found by applying {@code move(1, CalendarUnit.DAYS)}
     * on the result of this method. If the first day of week is Monday then
     * users should consider the alternative {@link CalendarWeek ISO calendar week}. </p>
     *
     * @param clock       the clock for evaluating the current calendar week
     * @param tzid        time zone in which the calendar week is valid
     * @param firstDay    first day of week
     * @return  the current calendar week as {@code DateInterval}
     * @throws  IllegalArgumentException if given timezone cannot be loaded
     * @see     net.time4j.SystemClock#INSTANCE
     * @see     Timezone#getID()
     * @see     Weekmodel#getFirstDayOfWeek()
     * @see     #move(long, IsoDateUnit)
     * @since   5.4
     */

    public static function ofCurrentWeek(
//        TimeSource clock,
//        TZID tzid,
//        Weekday firstDay
    ): self
    {

//        PlainDate today = Moment.from(clock.currentTime()).toZonalTimestamp(tzid).toDate();
//        PlainDate start = today.with(PlainDate.DAY_OF_WEEK.setToPreviousOrSame(firstDay));
//        PlainDate end = start.plus(6, CalendarUnit.DAYS);
//        return DateInterval.between(start, end);
    }

    /**
     * <p>Converts this instance to a timestamp interval with
     * dates from midnight to midnight. </p>
     *
     * <p>The resulting interval is half-open if this interval is finite. </p>
     *
     * @return  timestamp interval (from midnight to midnight)
     * @since   2.0
     */
    /*[deutsch]
     * <p>Wandelt diese Instanz in ein Zeitstempelintervall
     * mit Datumswerten von Mitternacht zu Mitternacht um. </p>
     *
     * <p>Das Ergebnisintervall ist halb-offen, wenn dieses Intervall
     * endlich ist. </p>
     *
     * @return  timestamp interval (from midnight to midnight)
     * @since   2.0
     */
    public function toFullDays(): LocalDateTimeInterval
    {
//
//        Boundary<PlainTimestamp> b1;
//        Boundary<PlainTimestamp> b2;
//
//        if (this.getStart().isInfinite()) {
//            b1 = Boundary.infinitePast();
//        } else {
//    PlainDate d1 = this.getStart().getTemporal();
//            PlainTimestamp t1;
//            if (this.getStart().isOpen()) {
//                t1 = d1.at(PlainTime.midnightAtEndOfDay());
//            } else {
//                t1 = d1.atStartOfDay();
//            }
//            b1 = Boundary.of(IntervalEdge.CLOSED, t1);
//        }
//
//if (this.getEnd().isInfinite()) {
//    b2 = Boundary.infiniteFuture();
//} else {
//    PlainDate d2 = this.getEnd().getTemporal();
//            PlainTimestamp t2;
//            if (this.getEnd().isOpen()) {
//                t2 = d2.atStartOfDay();
//            } else {
//                t2 = d2.at(PlainTime.midnightAtEndOfDay());
//            }
//            b2 = Boundary.of(IntervalEdge.OPEN, t2);
//        }
//
//return new TimestampInterval(b1, b2);

    }

    /**
     * <p>Converts this instance to a moment interval with date boundaries mapped
     * to the midnight cycle in given time zone. </p>
     *
     * <p>The resulting interval is half-open if this interval is finite. Note that sometimes
     * the moments of result intervals can deviate from midnight if midnight does not exist
     * due to daylight saving effects. </p>
     *
     * @param tzid        timezone identifier
     * @return  global timestamp intervall interpreted in given timezone
     * @see     GapResolver#NEXT_VALID_TIME
     * @see     OverlapResolver#EARLIER_OFFSET
     * @since   3.23/4.19
     */
    public function inTimezone(TimeZone $tzid): ZonedDateTimeInterval
    {

//        return this.toFullDays().in(
//            Timezone.of(tzid).with(GapResolver.NEXT_VALID_TIME.and(OverlapResolver.EARLIER_OFFSET)));

    }

    /**
     * <p>Yields the length of this interval in days. </p>
     *
     * @return  duration in days as long primitive
     * @throws  UnsupportedOperationException if this interval is infinite
     * @since   2.0
     * @see     #getDurationInYearsMonthsDays()
     * @see     #getDuration(CalendarUnit[]) getDuration(CalendarUnit...)
     */
    public function getLengthInDays(): int
    {

//        if (this.isFinite()) {
//            long days = CalendarUnit.DAYS.between(
//                this.getStart().getTemporal(),
//                this.getEnd().getTemporal());
//            if (this.getStart().isOpen()) {
//                days--;
//            }
//if (this.getEnd().isClosed()) {
//    days++;
//}
//return days;
//} else {
//    throw new UnsupportedOperationException(
//        "An infinite interval has no finite duration.");
    }

    /**
     * <p>Yields the length of this interval in years, months and days. </p>
     *
     * @return  duration in years, months and days
     * @throws  UnsupportedOperationException if this interval is infinite
     * @since   2.0
     * @see     #getLengthInDays()
     * @see     #getDuration(CalendarUnit[]) getDuration(CalendarUnit...)
     */
    public function getDurationInYearsMonthsDays(): Period
    {

//        PlainDate date = this.getTemporalOfOpenEnd();
//        boolean max = (date == null);
//
//        if (max) { // max reached
//            date = this.getEnd().getTemporal();
//        }
//
//Duration<CalendarUnit> result =
//    Duration.inYearsMonthsDays().between(
//        this.getTemporalOfClosedStart(),
//        date);
//
//        if (max) {
//            return result.plus(1, CalendarUnit.DAYS);
//        }
//
//        return result;

    }

    /**
     * <p>Yields the length of this interval in given calendrical units. </p>
     *
     * @param units   calendrical units as calculation base
     * @return duration in given units
     * @throws  UnsupportedOperationException if this interval is infinite
     * @since   2.0
     * @see     #getLengthInDays()
     * @see     #getDurationInYearsMonthsDays()
     */
    public function getDuration(
//        CalendarUnit... units
    ): Duration
    {

//        PlainDate date = this.getTemporalOfOpenEnd();
//        boolean max = (date == null);
//
//        if (max) { // max reached
//            date = this.getEnd().getTemporal();
//        }
//
//Duration < CalendarUnit> result =
//    Duration.in(units).between(
//        this.getTemporalOfClosedStart(),
//        date
//    );
//
//        if (max) {
//            return result.plus(1, CalendarUnit.DAYS);
//        }
//
//        return result;

    }

    /**
     * <p>Moves this interval along the time axis by given units. </p>
     *
     * @param amount  amount of units
     * @param unit    time unit for moving
     * @return  moved copy of this interval
     * @since   3.37/4.32
     */
    public function move(
//    long amount,
//        IsoDateUnit unit
    ): self
    {
//
//    if (amount == 0) {
//        return this;
//    }
//
//    Boundary<PlainDate> s;
//        Boundary<PlainDate> e;
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
//        return new DateInterval(s, e);

    }

    /**
     * <p>Obtains a stream iterating over every calendar date between given interval boundaries. </p>
     *
     * <p>This static method avoids the costs of constructing an instance of {@code DateInterval}. </p>
     *
     * @param start       start boundary - inclusive
     * @param end         end boundary - inclusive
     * @return  daily stream
     * @throws  IllegalArgumentException if start is after end
     * @see     #streamDaily()
     * @since   4.18
     */
    public static function streamDaily(
        LocalDate $start,
        LocalDate $end
    ): iterable {
//
//        long s = start.getDaysSinceEpochUTC();
//        long e = end.getDaysSinceEpochUTC();
//
//        if (s > e) {
//            throw new IllegalArgumentException("Start after end: " + start + "/" + end);
//        }
//
//return StreamSupport.stream(new DailySpliterator(start, s, e), false);

    }

    /**
     * <p>Obtains a stream iterating over every calendar date which is the result of addition of given duration
     * to start until the end of this interval is reached. </p>
     *
     * @param duration    duration which has to be added to the start multiple times
     * @return  stream consisting of distinct dates which are the result of adding the duration to the start
     * @throws  IllegalStateException if this interval is infinite or if there is no canonical form
     * @throws  IllegalArgumentException if the duration is not positive
     * @see     #toCanonical()
     * @see     #stream(Duration, PlainDate, PlainDate)
     * @since   4.18
     */
//    public Stream<PlainDate> stream(Duration<CalendarUnit> duration) {
    public function stream(
//        Duration<CalendarUnit> duration
    ): iterable
    {
//
//        if (this.isEmpty() && duration.isPositive()) {
//            return Stream.empty();
//        }
//
//        DateInterval interval = this.toCanonical();
//        PlainDate start = interval.getStartAsCalendarDate();
//        PlainDate end = interval.getEndAsCalendarDate();
//
//        if ((start == null) || (end == null)) {
//            throw new IllegalStateException("Streaming is not supported for infinite intervals.");
//        }
//
//        return DateInterval.stream(duration, start, end);

    }

    /**
     * <p>Obtains a stream iterating over every calendar date of the canonical form of this interval
     * and applies given exclusion filter. </p>
     *
     * <p>Example of exclusion of Saturday and Sunday: </p>
     *
     * <pre>
     *     DateInterval.between(
     *       PlainDate.of(2017, 2, 1),
     *       PlainDate.of(2017, 2, 8)
     *     ).streamExcluding(Weekday.SATURDAY.or(Weekday.SUNDAY)).forEach(System.out::println);
     * </pre>
     *
     * <p>All objects whose type is a subclass of {@code ChronoCondition} can be also used as parameter, for example
     * localized weekends by the expression {@code Weekmodel.of(locale).weekend()}. </p>
     *
     * @param exclusion   filter as predicate
     * @return  daily filtered stream
     * @throws  IllegalStateException if this interval is infinite or if there is no canonical form
     * @see     #toCanonical()
     * @see     net.time4j.engine.ChronoCondition
     * @since   4.24
     */
    public function streamExcluding(
        //Predicate<? super PlainDate> exclusion
    )
    {
//        return this.streamDaily().filter(exclusion.negate());
    }

    /**
     * <p>Obtains a stream iterating over every calendar date which is the result of addition of given duration
     * in week-based units to start until the end of this interval is reached. </p>
     *
     * @param weekBasedYears      duration component of week-based years
     * @param isoWeeks            duration component of calendar weeks (from Monday to Sunday)
     * @param days                duration component of ordinary calendar days
     * @return  stream consisting of distinct dates which are the result of adding the duration to the start
     * @throws  IllegalArgumentException    if there is any negative duration component or if there is
     *                                      no positive duration component at all
     * @throws  IllegalStateException       if this interval is infinite or if there is no canonical form
     * @see     #toCanonical()
     * @see     Weekcycle#YEARS
     * @since   4.18
     */
    /*[deutsch]
     * <p>Erzeugt einen {@code Stream}, der jeweils ein Kalenderdatum als Vielfaches der Dauer in
     * wochenbasierten Zeiteinheiten angewandt auf den Start und bis zum Ende dieses Intervalls geht. </p>
     *
     * @param   weekBasedYears      duration component of week-based years
     * @param   isoWeeks            duration component of calendar weeks (from Monday to Sunday)
     * @param   days                duration component of ordinary calendar days
     * @throws  IllegalStateException       if this interval is infinite or if there is no canonical form
     * @throws  IllegalArgumentException    if there is any negative duration component or if there is
     *                                      no positive duration component at all
     * @return  stream consisting of distinct dates which are the result of adding the duration to the start
     * @see     #toCanonical()
     * @see     Weekcycle#YEARS
     * @since   4.18
     */
    public function streamWeekBased(
//        int weekBasedYears,
//        int isoWeeks,
//        int days
    ): iterable
    {
//
//        if ((weekBasedYears < 0) || (isoWeeks < 0) || (days < 0)) {
//            throw new IllegalArgumentException("Found illegal negative duration component.");
//        }
//
//final long effYears = weekBasedYears;
//        final long effDays = 7L * isoWeeks + days;
//
//        if ((weekBasedYears == 0) && (effDays == 0)) {
//            throw new IllegalArgumentException("Cannot create stream with empty duration.");
//        }
//
//        if (this.isEmpty()) {
//            return Stream.empty();
//        }
//
//        DateInterval interval = this.toCanonical();
//        PlainDate start = interval.getStartAsCalendarDate();
//        PlainDate end = interval.getEndAsCalendarDate();
//
//        if ((start == null) || (end == null)) {
//            throw new IllegalStateException("Streaming is not supported for infinite intervals.");
//        }
//
//        if ((effYears == 0) && (effDays == 1)) {
//            return DateInterval.streamDaily(start, end);
//        }
//
//        long s = start.getDaysSinceEpochUTC();
//        long e = end.getDaysSinceEpochUTC();
//
//        long n = 1 + ((e - s) / (Math.addExact(Math.multiplyExact(effYears, 371), effDays))); // first estimate
//        PlainDate date;
//        long size;
//
//        do {
//            size = n;
//            long y = Math.multiplyExact(effYears, n);
//            long d = Math.multiplyExact(effDays, n);
//            date = start.plus(y, Weekcycle.YEARS).plus(d, CalendarUnit.DAYS);
//            n++;
//        } while (!date.isAfter(end));
//
//        if (size == 1) {
//            return Stream.of(start); // short-cut
//        }
//
//        return LongStream.range(0, size).mapToObj(
//                index -> start.plus(effYears * index, Weekcycle.YEARS).plus(effDays * index, CalendarUnit.DAYS));

    }

    /**
     * Obtains a random date within this interval. </p>
     *
     * @return  random date within this interval
     * @throws  IllegalStateException if this interval is infinite or empty or if there is no canonical form
     * @see     #toCanonical()
     * @since   5.0
     */
    public function random(): self
    {

//        DateInterval interval = this.toCanonical();
//
//        if (interval.isFinite() && !interval.isEmpty()) {
//            long randomNum =
//                ThreadLocalRandom.current().nextLong(
//                    interval.getStartAsCalendarDate().getDaysSinceEpochUTC(),
//                    interval.getEndAsCalendarDate().getDaysSinceEpochUTC() + 1);
//            return PlainDate.of(randomNum, EpochDays.UTC);
//        } else {
//    throw new IllegalStateException("Cannot get random date in an empty or infinite interval: " + this);

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
     * component instead). </p>
     *
     * <p>The infinity symbols &quot;-&quot; (past and future),
     * &quot;-&#x221E;&quot; (past), &quot;+&#x221E;&quot; (future),
     * &quot;-999999999-01-01&quot; und &quot;+999999999-12-31&quot;
     * can also be parsed as extension although strictly spoken ISO-8601
     * does not know or specify infinite intervals. Examples for supported
     * formats: </p>
     *
     * <pre>
     *  System.out.println(
     *      DateInterval.parseISO(&quot;2012-01-01/2014-06-20&quot;));
     *  // output: [2012-01-01/2014-06-20]
     *
     *  System.out.println(DateInterval.parseISO(&quot;2012-01-01/08-11&quot;));
     *  // output: [2012-01-01/2012-08-11]
     *
     *  System.out.println(DateInterval.parseISO(&quot;2012-W01-1/W06-4&quot;));
     *  // output: [2012-01-02/2012-02-09]
     *
     *  System.out.println(DateInterval.parseISO(&quot;2012-001/366&quot;));
     *  // output: [2012-01-01/2012-12-31]
     *
     *  System.out.println(DateInterval.parseISO(&quot;2012-001/+&#x221E;&quot;));
     *  // output: [2012-01-01/+&#x221E;)
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
    /*[deutsch]
     * <p>Interpretiert den angegebenen ISO-konformen Text als Intervall. </p>
     *
     * <p>Alle Stile werden unterst&uuml;tzt, n&auml;mlich Kalendardatum,
     * Ordinaldatum und Wochendatum, sowohl im Basisformat als auch im
     * erweiterten Format. Gemischte Datumsstile von Start und Ende
     * sind jedoch nicht erlaubt. Au&szlig;erdem darf eine der beiden
     * Komponenten Start und Ende als P-String vorliegen. Wenn nicht, dann
     * darf die Endkomponente auch in einer abgek&uuml;rzten Schreibweise
     * angegeben werden, in der weniger pr&auml;zise Elemente wie das
     * Kalenderjahr ausgelassen und von der Startkomponente &uuml;bernommen
     * werden. </p>
     *
     * <p>Die Unendlichkeitssymbole &quot;-&quot; (sowohl Vergangenheit als auch Zukunft),
     * &quot;-&#x221E;&quot; (Vergangenheit), &quot;+&#x221E;&quot; (Zukunft),
     * &quot;-999999999-01-01&quot; und &quot;+999999999-12-31&quot; werden ebenfalls
     * interpretiert, obwohl ISO-8601 keine unendlichen Intervalle kennt. Beispiele
     * f&uuml;r unterst&uuml;tzte Formate: </p>
     *
     * <pre>
     *  System.out.println(
     *      DateInterval.parseISO(&quot;2012-01-01/2014-06-20&quot;));
     *  // Ausgabe: [2012-01-01/2014-06-20]
     *
     *  System.out.println(DateInterval.parseISO(&quot;2012-01-01/08-11&quot;));
     *  // Ausgabe: [2012-01-01/2012-08-11]
     *
     *  System.out.println(DateInterval.parseISO(&quot;2012-W01-1/W06-4&quot;));
     *  // Ausgabe: [2012-01-02/2012-02-09]
     *
     *  System.out.println(DateInterval.parseISO(&quot;2012-001/366&quot;));
     *  // Ausgabe: [2012-01-01/2012-12-31]
     *
     *  System.out.println(DateInterval.parseISO(&quot;2012-001/+&#x221E;&quot;));
     *  // output: [2012-01-01/+&#x221E;)
     * </pre>
     *
     * <p>Intern wird das notwendige Intervallformat f&uuml;r reduzierte Formen dynamisch ermittelt. Ist
     * das Antwortzeitverhalten wichtiger, sollte einem statisch initialisierten konstanten Format der
     * Vorzug gegeben werden. </p>
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
//        if (text.isEmpty()) {
//            throw new IndexOutOfBoundsException("Empty text.");
//        }
//
//// prescan for format analysis
//int start = 0;
//		int n = Math.min(text.length(), 48);
//        boolean sameFormat = true;
//        int componentLength = 0;
//
//        for (int i = 1; i < n; i++) {
//    if (text.charAt(i) == '/') {
//        if (i + 1 == n) {
//            throw new ParseException("Missing end component.", n);
//        } else if (
//            (text.charAt(0) == 'P')
//            || ((text.charAt(0) == '-') && (i == 1 || text.charAt(1) == '\u221E'))
//        ) {
//            start = i + 1;
//            componentLength = n - i - 1;
//        } else if (
//            (text.charAt(i + 1) == 'P')
//            || ((text.charAt(i + 1) == '-') && (i + 2 == n))
//            || ((text.charAt(i + 1) == '+') && (i + 2 < n) && (text.charAt(i + 2) == '\u221E'))
//        ) {
//            componentLength = i;
//        } else {
//            sameFormat = (2 * i + 1 == n);
//            componentLength = i;
//        }
//        break;
//    }
//}
//
//        int literals = 0;
//        boolean ordinalStyle = false;
//        boolean weekStyle = false;
//
//        for (int i = start + 1; i < n; i++) {
//    char c = text.charAt(i);
//            if (c == '-') {
//                literals++;
//            } else if (c == 'W') {
//                weekStyle = true;
//                break;
//            } else if (c == '/') {
//                break;
//            }
//        }
//
//        boolean extended = (literals > 0);
//        char c = text.charAt(start);
//        componentLength -= 4;
//
//        if ((c == '+') || (c == '-')) {
//            componentLength -= 2;
//        }
//
//        if (!weekStyle) {
//            ordinalStyle = (
//                (literals == 1)
//                || ((literals == 0) && (componentLength == 3)));
//        }
//
//        // start format
//        ChronoFormatter<PlainDate> startFormat;
//
//        if (extended) {
//            if (ordinalStyle) {
//                startFormat = Iso8601Format.EXTENDED_ORDINAL_DATE;
//            } else if (weekStyle) {
//                startFormat = Iso8601Format.EXTENDED_WEEK_DATE;
//            } else {
//                startFormat = Iso8601Format.EXTENDED_CALENDAR_DATE;
//            }
//        } else {
//            if (ordinalStyle) {
//                startFormat = Iso8601Format.BASIC_ORDINAL_DATE;
//            } else if (weekStyle) {
//                startFormat = Iso8601Format.BASIC_WEEK_DATE;
//            } else {
//                startFormat = Iso8601Format.BASIC_CALENDAR_DATE;
//            }
//        }
//
//        // prepare component parsers
//        ChronoFormatter<PlainDate> endFormat = (sameFormat ? startFormat : null); // null means reduced iso format
//
//        // create interval
//        Parser parser = new Parser(startFormat, endFormat, extended, weekStyle, ordinalStyle);
//        return parser.parse(text);
    }

    // todo @see format(...) methods from Time4j classes
    public function toString(): string
    {

    }
}
