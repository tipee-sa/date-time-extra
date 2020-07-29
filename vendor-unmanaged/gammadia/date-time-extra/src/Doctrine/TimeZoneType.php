<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\TimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TimeZoneType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param TimeZone $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return $value->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return TimeZone::parse($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'timezone_offset_type';
    }
}
