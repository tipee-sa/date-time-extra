<?php

namespace Gammadia\DateTimeExtra;

class IntervalParseException extends \RuntimeException
{
    public static function uniqueDuration(string $textToParse) : self
    {
        return new self('Text cannot be parsed to a Duration/Duration format: ' . $textToParse);
    }

    public static function durationIncompatibleWithInfinity(string $textToParse) : self
    {
        return new self('Text cannot be parsed to a Period/- or -/Duration format: ' . $textToParse);
    }
}
