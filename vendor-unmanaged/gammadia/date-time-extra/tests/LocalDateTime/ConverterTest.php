<?php

namespace Gammadia\DateTimeExtra\Test\Unit\LocalDateTime;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\TimeZoneOffset;
use Gammadia\DateTimeExtra\LocalDateTime\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    public function testToInstant(): void
    {
        $localDateTime = LocalDateTime::of(2016, 2, 14, 23, 59, 59, 59);

        self::assertEquals(
            '2016-02-14T23:59:59.000000059Z',
            (string)Converter::toInstant($localDateTime, TimeZoneOffset::utc())
        );

        self::assertEquals(
            '2016-02-14T15:59:59.000000059Z',
            (string)Converter::toInstant($localDateTime, TimeZoneOffset::of(8, 0, 0))
        );

        self::assertEquals(
            '2016-02-15T04:29:59.000000059Z',
            (string)Converter::toInstant($localDateTime, TimeZoneOffset::of(-4, -30, 0))
        );
    }

}
