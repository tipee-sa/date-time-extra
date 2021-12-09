<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalTime;
use Brick\DateTime\MonthDay;
use Gammadia\DateTimeExtra\Normalizer\MonthDayNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class MonthDayNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new MonthDayNormalizer();

        $iso = '--12-31';
        $duration = MonthDay::parse($iso);

        self::assertTrue($normalizer->supportsNormalization($duration));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame($iso, $normalizer->normalize($duration));
    }

    public function testDenormalize(): void
    {
        $normalizer = new MonthDayNormalizer();
        $data = '--01-01';

        self::assertTrue($normalizer->supportsDenormalization($data, MonthDay::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, MonthDay::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('01-01--', MonthDay::class);
    }
}
