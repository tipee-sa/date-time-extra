<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\Instant;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\Normalizer\InstantNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class InstantNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new InstantNormalizer();
        $instant = Instant::of(90);

        self::assertTrue($normalizer->supportsNormalization($instant));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('1970-01-01T00:01:30Z', $normalizer->normalize($instant));
    }

    public function testDenormalize(): void
    {
        $normalizer = new InstantNormalizer();
        $data = '1970-01-01T00:01:30Z';

        self::assertTrue($normalizer->supportsDenormalization($data, Instant::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame((string) Instant::of(90), (string) $normalizer->denormalize($data, Instant::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('-10', Instant::class);
    }
}
