<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Normalizer;

use Brick\DateTime\LocalTime;
use Brick\DateTime\YearWeek;
use Gammadia\DateTimeExtra\Normalizer\YearWeekNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class YearWeekNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new YearWeekNormalizer();
        $duration = YearWeek::of(2021, 7);

        self::assertTrue($normalizer->supportsNormalization($duration));
        self::assertFalse($normalizer->supportsNormalization(LocalTime::parse('10:15')));
        self::assertSame('2021-W07', $normalizer->normalize($duration));
    }

    public function testDenormalize(): void
    {
        $normalizer = new YearWeekNormalizer();
        $data = '2019-W02';

        self::assertTrue($normalizer->supportsDenormalization($data, YearWeek::class));
        self::assertFalse($normalizer->supportsDenormalization($data, LocalTime::class));
        self::assertSame($data, (string) $normalizer->denormalize($data, YearWeek::class));

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('2019-02', YearWeek::class);
    }
}
