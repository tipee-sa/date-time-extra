<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\Normalizer\LocalTimeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class LocalTimeNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new LocalTimeNormalizer();
        $localTime = LocalTime::of(10, 15);

        self::assertTrue($normalizer->supportsNormalization($localTime));
        self::assertFalse($normalizer->supportsNormalization(LocalDate::of(2013, 1, 1)));
        self::assertSame('10:15', $normalizer->normalize($localTime));
    }

    public function testDenormalize(): void
    {
        $normalizer = new LocalTimeNormalizer();
        $data = '10:15';

        self::assertTrue($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalDate::class));
        self::assertTrue(LocalTime::of(10, 15)->isEqualTo($normalizer->denormalize($data, LocalTime::class)));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('25:00', LocalTime::class);
    }
}
