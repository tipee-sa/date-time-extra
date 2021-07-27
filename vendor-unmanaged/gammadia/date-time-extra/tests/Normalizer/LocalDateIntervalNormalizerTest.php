<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\LocalDateInterval;
use Gammadia\DateTimeExtra\Normalizer\LocalDateIntervalNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class LocalDateIntervalNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new LocalDateIntervalNormalizer();
        $localDateTimeInterval = LocalDateInterval::between(LocalDate::of(2013, 01, 01), LocalDate::of(2013, 01, 01));

        self::assertTrue($normalizer->supportsNormalization($localDateTimeInterval));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('2013-01-01/2013-01-01', $normalizer->normalize($localDateTimeInterval));
    }

    public function testDenormalize(): void
    {
        $normalizer = new LocalDateIntervalNormalizer();
        $data = '2013-01-01/2013-01-01';

        self::assertTrue($normalizer->supportsDenormalization($data, LocalDateInterval::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, LocalDateInterval::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('2013-13-32/2013-13-32', LocalDateInterval::class);
    }
}
