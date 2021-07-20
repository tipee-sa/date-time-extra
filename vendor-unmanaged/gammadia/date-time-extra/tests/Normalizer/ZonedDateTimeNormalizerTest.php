<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\TimeZone;
use Brick\DateTime\ZonedDateTime;
use Gammadia\DateTimeExtra\Normalizer\ZonedDateTimeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class ZonedDateTimeNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new ZonedDateTimeNormalizer();
        $zonedDateTime = ZonedDateTime::of(LocalDateTime::of(2013, 1, 1), TimeZone::parse('Europe/Zurich'));

        self::assertTrue($normalizer->supportsNormalization($zonedDateTime));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('2013-01-01T00:00+01:00[Europe/Zurich]', $normalizer->normalize($zonedDateTime));
    }

    public function testDenormalize(): void
    {
        $normalizer = new ZonedDateTimeNormalizer();
        $data = '2013-01-01T00:00+01:00[Europe/Zurich]';

        self::assertTrue($normalizer->supportsDenormalization($data, ZonedDateTime::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertTrue(ZonedDateTime::of(LocalDateTime::of(2013, 1, 1), TimeZone::parse('Europe/Zurich'))->isEqualTo($normalizer->denormalize($data, ZonedDateTime::class)));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('2013-01-01T00:00+01:00[Europe/Zurrich]', ZonedDateTime::class);
    }
}
