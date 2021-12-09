<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\Normalizer\LocalDateNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class LocalDateNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new LocalDateNormalizer();
        $localDate = LocalDate::of(2013, 1, 1);

        self::assertTrue($normalizer->supportsNormalization($localDate));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('2013-01-01', $normalizer->normalize($localDate));
    }

    public function testDenormalize(): void
    {
        $normalizer = new LocalDateNormalizer();
        $data = '2013-01-01';

        self::assertTrue($normalizer->supportsDenormalization($data, LocalDate::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, LocalDate::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('2013-13-32', LocalDate::class);
    }
}
