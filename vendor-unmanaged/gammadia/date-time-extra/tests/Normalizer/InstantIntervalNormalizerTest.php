<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\InstantInterval;
use Gammadia\DateTimeExtra\Normalizer\InstantIntervalNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class InstantIntervalNormalizerTest extends TestCase
{
//    public function testNormalize(): void
//    {
//        $normalizer = new InstantIntervalNormalizer();
//        $instantInterval = InstantInterval::between(Instant::of(1), Instant::of(90));
//
//        self::assertTrue($normalizer->supportsNormalization($instantInterval));
//        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
//        self::assertSame('1970-01-01T00:00:01Z/1970-01-01T00:01:30Z', $normalizer->normalize($instantInterval));
//    }
//
//    public function testDenormalize(): void
//    {
//        $normalizer = new InstantIntervalNormalizer();
//        $data = '1970-01-01T00:00:01Z/1970-01-01T00:01:30Z';
//
//        self::assertTrue($normalizer->supportsDenormalization($data, InstantInterval::class));
//        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
//        self::assertTrue(InstantInterval::between(Instant::of(1), Instant::of(90))->isEqualTo($normalizer->denormalize($data, InstantInterval::class)));
//
//        $this->expectException(NotNormalizableValueException::class);
//        $normalizer->denormalize('1970-01-01T00:00:01Z/1970-01-01T00:01:30Z', LocalDate::class);
//    }
}
