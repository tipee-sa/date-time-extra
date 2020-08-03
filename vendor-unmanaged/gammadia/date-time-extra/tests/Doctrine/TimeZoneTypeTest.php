<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Doctrine;

use Brick\DateTime\TimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Gammadia\DateTimeExtra\Doctrine\TimeZoneType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TimeZoneTypeTest extends TestCase
{
    /** @var TimeZoneType */
    private $type;

    /** @var AbstractPlatform */
    private $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(TimeZoneType::class))->newInstanceWithoutConstructor();
        $this->platform = new MySqlPlatform();
    }

    public function testConvertToDatabaseValue(): void
    {
        self::assertSame(
            'Europe/Zurich',
            $this->type->convertToDatabaseValue(
                TimeZone::parse('Europe/Zurich'),
                $this->platform
            )
        );

        self::assertSame(
            'Z',
            $this->type->convertToDatabaseValue(
                TimeZone::utc(),
                $this->platform
            )
        );
    }

    public function testGetSQLDeclaration(): void
    {
        self::assertSame(
            'VARCHAR(255)',
            $this->type->getSQLDeclaration([], $this->platform)
        );
    }

    public function testConvertToPHPValue(): void
    {
        self::assertSame(
            TimeZone::parse('Europe/Zurich')->getId(),
            $this->type->convertToPHPValue('Europe/Zurich', $this->platform)->getId()
        );
    }

    public function testGetName(): void
    {
        self::assertSame('timezone', $this->type->getName());
    }
}
