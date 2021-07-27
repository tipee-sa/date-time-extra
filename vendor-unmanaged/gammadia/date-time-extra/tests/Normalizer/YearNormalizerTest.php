<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalTime;
use Brick\DateTime\Year;
use Gammadia\DateTimeExtra\Normalizer\YearNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class YearNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new YearNormalizer();

        $iso = '2021';
        $duration = Year::of((int) $iso);

        self::assertTrue($normalizer->supportsNormalization($duration));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame($iso, $normalizer->normalize($duration));
    }

    public function testDenormalize(): void
    {
        $normalizer = new YearNormalizer();
        $data = '1952';

        self::assertTrue($normalizer->supportsDenormalization($data, Year::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, Year::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('123819237192873', Year::class);
    }
}
