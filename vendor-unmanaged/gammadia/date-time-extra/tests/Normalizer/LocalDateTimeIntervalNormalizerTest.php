<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use Gammadia\DateTimeExtra\Normalizer\LocalDateTimeIntervalNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class LocalDateTimeIntervalNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new LocalDateTimeIntervalNormalizer();
        $localDateTimeInterval = LocalDateTimeInterval::between(LocalDateTime::of(2013, 01, 01, 1, 30, 30), LocalDateTime::of(2013, 01, 01, 2, 30, 30));

        self::assertTrue($normalizer->supportsNormalization($localDateTimeInterval));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('2013-01-01T01:30:30/2013-01-01T02:30:30', $normalizer->normalize($localDateTimeInterval));
    }

    public function testDenormalize(): void
    {
        $normalizer = new LocalDateTimeIntervalNormalizer();
        $data = '2013-01-01T01:30:30/2013-01-01T02:30:30';

        self::assertTrue($normalizer->supportsDenormalization($data, LocalDateTimeInterval::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertTrue(LocalDateTimeInterval::between(LocalDateTime::of(2013, 1, 1, 1, 30, 30), LocalDateTime::of(2013, 1, 1, 2, 30, 30))->isEqualTo($normalizer->denormalize($data, LocalDateTimeInterval::class)));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('2013-13-32T00:00:61/2013-13-32T02:00:61', LocalDateTimeInterval::class);
    }
}
