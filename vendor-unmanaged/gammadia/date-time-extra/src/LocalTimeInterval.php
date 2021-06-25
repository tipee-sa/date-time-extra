<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Throwable;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class LocalTimeInterval implements JsonSerializable
{
    /**
     * @var string
     */
    private const SEPARATOR = '/';

    /**
     * @var LocalTime
     */
    #[ORM\Column(type: 'local_time')]
    private $timepoint;

    /**
     * @var Duration
     */
    #[ORM\Column(type: 'duration')]
    private $duration;

    private function __construct(LocalTime $timepoint, Duration $duration)
    {
        Assert::true(
            $duration->isPositiveOrZero(),
            sprintf('Negative durations are not supported by %s', self::class)
        );

        $this->timepoint = $timepoint;
        $this->duration = $duration;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /*
     * Named constructors
     */

    public static function empty(?LocalTime $timepoint = null): self
    {
        return new self($timepoint ?? LocalTime::min(), Duration::zero());
    }

    public static function ofDays(int $days): self
    {
        return new self(LocalTime::min(), Duration::ofDays($days));
    }

    public static function between(LocalTime $timepoint, Duration $duration): self
    {
        return new self($timepoint, $duration);
    }

    /**
     * @param string $textToParse A LocalTime + optional Duration (12:34/PT2H)
     */
    public static function parse(string $textToParse): self
    {
        try {
            Assert::contains($textToParse, self::SEPARATOR);
            [$firstPart, $secondPart] = explode(self::SEPARATOR, $textToParse, 2);

            return new self(LocalTime::parse($firstPart), Duration::parse($secondPart));
        } catch (Throwable $throwable) {
            throw IntervalParseException::localTimeInterval($textToParse, $throwable);
        }
    }

    /*
     * Converters methods
     */

    public function atDate(LocalDate $date): LocalDateTimeInterval
    {
        $start = $this->timepoint->atDate($date);

        return LocalDateTimeInterval::between($start, $start->plusDuration($this->duration));
    }

    public function toString(): string
    {
        return $this->timepoint . self::SEPARATOR . $this->duration;
    }

    /*
     * Transformers methods
     */

    public function withTimepoint(LocalTime $timepoint): self
    {
        if ($timepoint->isEqualTo($this->timepoint)) {
            return $this;
        }

        return new self($timepoint, $this->duration);
    }

    public function withDuration(Duration $duration): self
    {
        if ($duration->isEqualTo($this->duration)) {
            return $this;
        }

        return new self($this->timepoint, $duration);
    }

    public function move(Duration $duration): self
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this->withTimepoint($this->timepoint->plusDuration($duration));
    }

    public function collapse(): self
    {
        return self::empty($this->timepoint);
    }

    public function toFullDays(): self
    {
        $hasRemainder = !$this->timepoint->isEqualTo(LocalTime::min())
            || 0 !== $this->duration->toMillis() % (LocalTime::SECONDS_PER_DAY * LocalTime::MILLIS_PER_SECOND);
        $duration = Duration::ofDays(max(1, $this->duration->toDays() + (int) $hasRemainder));

        return new self(LocalTime::min(), $duration);
    }

    /*
     * Testers methods
     */

    public function isFullDays(): bool
    {
        return $this->isEqualTo($this->toFullDays());
    }

    public function isEmpty(): bool
    {
        return $this->duration->isZero();
    }

    /*
     * Comparators methods
     */

    public function isEqualTo(self $other): bool
    {
        return $this->timepoint->isEqualTo($other->timepoint) && $this->duration->isEqualTo($other->duration);
    }
}
