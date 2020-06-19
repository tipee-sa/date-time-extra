<?php

namespace Gammadia\DateTimeExtra\Test\Unit;

use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\LocalTimeInterval;
use PHPUnit\Framework\TestCase;

class LocalTimeIntervalTest extends TestCase
{
    public function testLocalInterval(): void
    {
        $a = LocalTime::of(23, 59, 59);

        $timeInterval = new LocalTimeInterval();

        self::assertNotNull($timeInterval);
    }
}
