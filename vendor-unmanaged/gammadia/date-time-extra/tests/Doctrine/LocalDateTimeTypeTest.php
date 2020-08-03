<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Doctrine;

use Brick\DateTime\LocalDateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Gammadia\DateTimeExtra\Doctrine\LocalDateTimeType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LocalDateTimeTypeTest extends TestCase
{
    /** @var LocalDateTimeType */
    private $type;

    /** @var AbstractPlatform */
    private $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(LocalDateTimeType::class))->newInstanceWithoutConstructor();
        $this->platform = new MySqlPlatform();
    }

    public function testGetSQLDeclaration(): void
    {
        self::assertSame(
            'DATETIME',
            $this->type->getSQLDeclaration([], $this->platform)
        );
    }

    public function testConvertToPHPValue(): void
    {
        /** @var LocalDateTime $localDateTime */
        $localDateTime = $this->type->convertToPHPValue('2019-02-02 12:30:30', $this->platform);

        self::assertSame('2019-02-02T12:30:30', $localDateTime->jsonSerialize());
    }

    public function testGetName(): void
    {
        self::assertSame('local_datetime', $this->type->getName());
    }
}
