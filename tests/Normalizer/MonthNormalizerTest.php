<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalTime;
use Brick\DateTime\Month;
use Gammadia\DateTimeExtra\Normalizer\MonthNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class MonthNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new MonthNormalizer();
        $month = Month::of(4);

        self::assertTrue($normalizer->supportsNormalization($month));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('april', $normalizer->normalize($month));
    }

    public function testDenormalize(): void
    {
        $normalizer = new MonthNormalizer();
        $data = 'february';

        self::assertTrue($normalizer->supportsDenormalization($data, Month::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertTrue(Month::of(Month::FEBRUARY)->isEqualTo($normalizer->denormalize($data, Month::class)));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('monday', Month::class);
    }
}
