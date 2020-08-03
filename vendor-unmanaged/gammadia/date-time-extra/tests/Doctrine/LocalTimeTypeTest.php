<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Doctrine;

use Brick\DateTime\LocalTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Gammadia\DateTimeExtra\Doctrine\LocalTimeType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LocalTimeTypeTest extends TestCase
{
    /** @var LocalTimeType */
    private $type;

    /** @var AbstractPlatform */
    private $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(LocalTimeType::class))->newInstanceWithoutConstructor();
        $this->platform = new MySqlPlatform();
    }

    public function testGetSQLDeclaration(): void
    {
        self::assertSame(
            'TIME',
            $this->type->getSQLDeclaration([], $this->platform)
        );
    }

    public function testConvertToPHPValue(): void
    {
        /** @var LocalTime $localDate */
        $localDate = $this->type->convertToPHPValue('13:37', $this->platform);

        self::assertSame('13:37', $localDate->jsonSerialize());
    }

    public function testGetName(): void
    {
        self::assertSame('local_time', $this->type->getName());
    }
}
