<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Gammadia\DateTimeExtra\Doctrine\LocalDateTimeType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LocalDateTimeTypeTest extends TestCase
{
    private LocalDateTimeType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(LocalDateTimeType::class))->newInstanceWithoutConstructor();
        $this->platform = new MySqlPlatform();
    }

    public function testGetSQLDeclaration(): void
    {
        self::assertSame('DATETIME', $this->type->getSQLDeclaration([], $this->platform));
    }

    public function testConvertToPHPValue(): void
    {
        $localDateTime = $this->type->convertToPHPValue('2019-02-02 12:30:30', $this->platform);

        self::assertNotNull($localDateTime);
        self::assertSame('2019-02-02T12:30:30', (string) $localDateTime);
    }

    public function testGetName(): void
    {
        self::assertSame('local_datetime', $this->type->getName());
    }
}
