<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Exceptions;

use RuntimeException;
use Throwable;

final class IntervalParseException extends RuntimeException
{
    public static function uniqueDuration(string $textToParse): self
    {
        return new self('Text cannot be parsed to a Duration/Duration format: ' . $textToParse);
    }

    public static function durationIncompatibleWithInfinity(string $textToParse): self
    {
        return new self('Text cannot be parsed to a Period/- or -/Duration format: ' . $textToParse);
    }

    public static function localTimeInterval(string $textToParse, ?Throwable $throwable = null): self
    {
        return new self('Text cannot be parsed to a LocalTime/Duration format: ' . $textToParse, 0, $throwable);
    }
}
