<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\TimeZoneOffset;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TimeZoneOffsetType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param TimeZoneOffset $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return $value->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'timezone_offset_type';
    }
}
