<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\DayOfWeek;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\Normalizer\DayOfWeekNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class DayOfWeekNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new DayOfWeekNormalizer();
        $dayOfWeek = DayOfWeek::of(4);

        self::assertTrue($normalizer->supportsNormalization($dayOfWeek));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('thursday', $normalizer->normalize($dayOfWeek));
    }

    public function testDenormalize(): void
    {
        $normalizer = new DayOfWeekNormalizer();
        $data = 'sunday';

        self::assertTrue($normalizer->supportsDenormalization($data, DayOfWeek::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertTrue(DayOfWeek::sunday()->isEqualTo($normalizer->denormalize($data, DayOfWeek::class)));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('funday', DayOfWeek::class);
    }
}
