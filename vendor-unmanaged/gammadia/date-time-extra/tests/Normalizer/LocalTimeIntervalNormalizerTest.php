<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\LocalTimeInterval;
use Gammadia\DateTimeExtra\Normalizer\LocalTimeIntervalNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class LocalTimeIntervalNormalizerTest extends TestCase
{
//    public function testNormalize(): void
//    {
//        $normalizer = new LocalTimeIntervalNormalizer();
//        $localTimeInterval = LocalTimeInterval::between(LocalTime::of(10, 15), LocalTime::of(12, 30));
//
//        self::assertTrue($normalizer->supportsNormalization($localTimeInterval));
//        self::assertFalse($normalizer->supportsNormalization(LocalDate::of(2013, 1, 1)));
//        self::assertSame('10:15/12:30', $normalizer->normalize($localTimeInterval));
//    }
//
//    public function testDenormalize(): void
//    {
//        $normalizer = new LocalTimeIntervalNormalizer();
//        $data = '10:15/12:30';
//
//        self::assertTrue($normalizer->supportsDenormalization($data, LocalTimeInterval::class));
//        self::assertFalse($normalizer->supportsDenormalization($data, LocalDate::class));
//        self::assertTrue(LocalTimeInterval::between(LocalTime::of(10, 15), LocalTime::of(12, 30))->isEqualTo($normalizer->denormalize($data, LocalTimeInterval::class)));
//
//        $this->expectException(NotNormalizableValueException::class);
//        $normalizer->denormalize('10:15/25:00', LocalTimeInterval::class);
//    }
}
