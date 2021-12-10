<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\TimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class TimeZoneType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    /**
     * @param TimeZone|null $value
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value) {
            return $value->getId();
        }

        return null;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?TimeZone
    {
        if ($value) {
            return TimeZone::parse($value);
        }

        return null;
    }

    public function getName(): string
    {
        return 'timezone';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
