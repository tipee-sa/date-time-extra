<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Doctrine;

use Brick\DateTime\TimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Gammadia\DateTimeExtra\Doctrine\TimeZoneType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class TimeZoneTypeTest extends TestCase
{
    private TimeZoneType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(TimeZoneType::class))->newInstanceWithoutConstructor();
        $this->platform = new MySqlPlatform();
    }

    public function testConvertToDatabaseValue(): void
    {
        $timezone = 'Europe/Zurich';
        self::assertSame($timezone, $this->type->convertToDatabaseValue(TimeZone::parse($timezone), $this->platform));

        self::assertSame('Z', $this->type->convertToDatabaseValue(TimeZone::utc(), $this->platform));
    }

    public function testGetSQLDeclaration(): void
    {
        self::assertSame('VARCHAR(255)', $this->type->getSQLDeclaration([], $this->platform));
    }

    public function testConvertToPHPValue(): void
    {
        $timezone = 'Europe/Zurich';
        $convertedValue = $this->type->convertToPHPValue($timezone, $this->platform);

        self::assertNotNull($convertedValue);
        self::assertSame(TimeZone::parse($timezone)->getId(), $convertedValue->getId());
    }

    public function testGetName(): void
    {
        self::assertSame('timezone', $this->type->getName());
    }
}
