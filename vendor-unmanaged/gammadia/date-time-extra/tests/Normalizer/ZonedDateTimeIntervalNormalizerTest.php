<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\ZonedDateTime;
use Gammadia\DateTimeExtra\Normalizer\ZonedDateTimeIntervalNormalizer;
use Gammadia\DateTimeExtra\ZonedDateTimeInterval;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class ZonedDateTimeIntervalNormalizerTest extends TestCase
{
//    public function testNormalize(): void
//    {
//        $normalizer = new ZonedDateTimeIntervalNormalizer();
//        $timezone = \Brick\DateTime\TimeZone::parse('Europe/Zurich');
//        $zonedDateTimeInterval = ZonedDateTimeInterval::between(ZonedDateTime::of(LocalDateTime::of(2013, 1, 1), $timezone), ZonedDateTime::of(LocalDateTime::of(2013, 1, 2), $timezone));
//
//        self::assertTrue($normalizer->supportsNormalization($zonedDateTimeInterval));
//        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
//        self::assertSame('2013-01-01T00:00+01:00[Europe/Zurich]/2013-01-02T00:00+01:00[Europe/Zurich]', $normalizer->normalize($zonedDateTimeInterval));
//    }
//
//    public function testDenormalize(): void
//    {
//        $normalizer = new ZonedDateTimeIntervalNormalizer();
//        $timezone = \Brick\DateTime\TimeZone::parse('Europe/Zurich');
//        $data = '2013-01-01T00:00+01:00[Europe/Zurich]/2013-01-02T00:00+01:00[Europe/Zurich]';
//
//        self::assertTrue($normalizer->supportsDenormalization($data, ZonedDateTimeInterval::class));
//        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
//        self::assertTrue(ZonedDateTimeInterval::between(ZonedDateTime::of(LocalDateTime::of(2013, 1, 1), $timezone), ZonedDateTime::of(LocalDateTime::of(2013, 1, 2), $timezone))->isEqualTo($normalizer->denormalize($data, ZonedDateTimeInterval::class)));
//
//        $this->expectException(NotNormalizableValueException::class);
//        $normalizer->denormalize('2013-01-01T00:00+01:00[Europe/Zurich]/2013-01-02T00:00+01:00[Europe/Zurrich]', ZonedDateTimeInterval::class);
//    }
}
