<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Test\Unit\Doctrine;



use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Gammadia\DateTimeExtra\Doctrine\LocalDateType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LocalDateTypeTest extends TestCase
{
    /** @var LocalDateType */
    private $type;

    /** @var AbstractPlatform */
    private $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(LocalDateType::class))->newInstanceWithoutConstructor();
        $this->platform = new MySqlPlatform();
    }

    public function testGetSQLDeclaration()
    {
        self::assertSame(
            "DATETIME",
            $this->type->getSQLDeclaration([], $this->platform)
        );
    }

    public function testConvertToPHPValue(): void
    {
        /** @var LocalDate $localDate */
        $localDate = $this->type->convertToPHPValue('2019-02-02 ', $this->platform);

        self::assertSame('2019-02-02T12:30:30', $localDate->jsonSerialize());
    }
}
