<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\Normalizer\DurationNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class DurationNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new DurationNormalizer();

        $iso = 'PT1H2M3S';
        $duration = Duration::parse($iso);

        self::assertTrue($normalizer->supportsNormalization($duration));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame($iso, $normalizer->normalize($duration));
    }

    public function testDenormalize(): void
    {
        $normalizer = new DurationNormalizer();
        $data = 'PT1H2M3S';

        self::assertTrue($normalizer->supportsDenormalization($data, Duration::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, Duration::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('P1Y2M3D', Duration::class);
    }
}
