<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalTime;
use Brick\DateTime\YearMonth;
use Gammadia\DateTimeExtra\Normalizer\YearMonthNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class YearMonthNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new YearMonthNormalizer();

        $iso = '2021-07';
        $duration = YearMonth::parse($iso);

        self::assertTrue($normalizer->supportsNormalization($duration));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame($iso, $normalizer->normalize($duration));
    }

    public function testDenormalize(): void
    {
        $normalizer = new YearMonthNormalizer();
        $data = '2019-02';

        self::assertTrue($normalizer->supportsDenormalization($data, YearMonth::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, YearMonth::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('02-2019', YearMonth::class);
    }
}
