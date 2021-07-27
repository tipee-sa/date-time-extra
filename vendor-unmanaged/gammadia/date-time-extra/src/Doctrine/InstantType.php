<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\Instant;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\TimeZone;
use Brick\DateTime\ZonedDateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class InstantType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTimeTypeDeclarationSQL($column);
    }

    /**
     * @param Instant|null $value
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        return ZonedDateTime::ofInstant($value, TimeZone::utc())
            ->toDateTime()
            ->format($platform->getDateTimeFormatString());
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Instant
    {
        if ($value) {
            return LocalDateTime::fromDateTime(new \DateTime($value))
                ->atTimeZone(TimeZone::utc())
                ->getInstant();
        }

        return null;
    }

    public function getName(): string
    {
        return 'instant';
    }
}
