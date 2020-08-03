<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Doctrine;

use Brick\DateTime\TimeZoneOffset;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Gammadia\DateTimeExtra\Doctrine\TimeZoneOffsetType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class TimeZoneOffsetTypeTest extends TestCase
{
    /** @var TimeZoneOffsetType */
    private $type;

    /** @var AbstractPlatform */
    private $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(TimeZoneOffsetType::class))->newInstanceWithoutConstructor();
        $this->platform = new MySqlPlatform();
    }

    public function testConvertToDatabaseValue(): void
    {
        self::assertSame(
            '+01:00',
            $this->type->convertToDatabaseValue(
                TimeZoneOffset::of(1),
                $this->platform
            )
        );

        self::assertSame(
            'Z',
            $this->type->convertToDatabaseValue(
                TimeZoneOffset::utc(),
                $this->platform
            )
        );
    }

    public function testConvertToPHPValue(): void
    {
        /** @var TimeZoneOffset $timeZoneOffset */
        $timeZoneOffset = $this->type->convertToPHPValue('+01:00', $this->platform);

        self::assertSame('+01:00', $timeZoneOffset->getId());
    }
}
