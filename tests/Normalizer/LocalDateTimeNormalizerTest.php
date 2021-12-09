<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\Normalizer\LocalDateTimeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class LocalDateTimeNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new LocalDateTimeNormalizer();
        $localDateTime = LocalDateTime::of(2013, 01, 01, 1, 30, 30);

        self::assertTrue($normalizer->supportsNormalization($localDateTime));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('2013-01-01T01:30:30', $normalizer->normalize($localDateTime));
    }

    public function testDenormalize(): void
    {
        $normalizer = new LocalDateTimeNormalizer();
        $data = '2013-01-01T01:30:30';

        self::assertTrue($normalizer->supportsDenormalization($data, LocalDateTime::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, LocalDateTime::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('2013-13-32T00:00:61', LocalDateTime::class);
    }
}
