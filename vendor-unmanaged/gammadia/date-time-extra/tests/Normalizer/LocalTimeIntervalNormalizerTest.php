<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalTime;
use Gammadia\DateTimeExtra\LocalTimeInterval;
use Gammadia\DateTimeExtra\Normalizer\LocalTimeIntervalNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class LocalTimeIntervalNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new LocalTimeIntervalNormalizer();
        $localTimeInterval = LocalTimeInterval::between(LocalTime::parse('12:34'), Duration::ofHours(2));

        self::assertTrue($normalizer->supportsNormalization($localTimeInterval));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('12:34/PT2H', $normalizer->normalize($localTimeInterval));
    }

    public function testDenormalize(): void
    {
        $normalizer = new LocalTimeIntervalNormalizer();
        $data = '12:34/PT2H';

        self::assertTrue($normalizer->supportsDenormalization($data, LocalTimeInterval::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, LocalTimeInterval::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('08:00/16:00', LocalTimeInterval::class);
    }
}
