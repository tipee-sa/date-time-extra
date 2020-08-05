<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\LocalDateInterval;
use Gammadia\DateTimeExtra\Normalizer\LocalDateIntervalNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class LocalDateIntervalNormalizerTest extends TestCase
{
//    public function testNormalize(): void
//    {
//        $normalizer = new LocalDateIntervalNormalizer();
//        $localDateInterval = LocalDateInterval::between(LocalDate::of(2013, 1, 1), LocalDate::of(2013, 1, 2));
//
//        self::assertTrue($normalizer->supportsNormalization($localDateInterval));
//        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
//        self::assertSame('2013-01-01/2013-01-02', $normalizer->normalize($localDateInterval));
//    }
//
//    public function testDenormalize(): void
//    {
//        $normalizer = new LocalDateIntervalNormalizer();
//        $data = '2013-01-01/2013-01-02';
//
//        self::assertTrue($normalizer->supportsDenormalization($data, LocalDateInterval::class));
//        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
//        self::assertTrue(LocalDateInterval::between(LocalDate::of(2013, 1, 1), LocalDate::of(2013, 1, 2))->isEqualTo($normalizer->denormalize($data, LocalDateInterval::class)));
//
//        $this->expectException(NotNormalizableValueException::class);
//        $normalizer->denormalize('2013-01-01/2013-13-32', LocalDateInterval::class);
//    }
}
