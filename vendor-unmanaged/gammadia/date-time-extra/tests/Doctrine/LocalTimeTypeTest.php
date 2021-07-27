<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Gammadia\DateTimeExtra\Doctrine\LocalTimeType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LocalTimeTypeTest extends TestCase
{
    private LocalTimeType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(LocalTimeType::class))->newInstanceWithoutConstructor();
        $this->platform = new MySqlPlatform();
    }

    public function testGetSQLDeclaration(): void
    {
        self::assertSame('TIME', $this->type->getSQLDeclaration([], $this->platform));
    }

    public function testConvertToPHPValue(): void
    {
        $localDate = $this->type->convertToPHPValue('13:37', $this->platform);

        self::assertNotNull($localDate);
        self::assertSame('13:37', (string) $localDate);
    }

    public function testGetName(): void
    {
        self::assertSame('local_time', $this->type->getName());
    }
}
