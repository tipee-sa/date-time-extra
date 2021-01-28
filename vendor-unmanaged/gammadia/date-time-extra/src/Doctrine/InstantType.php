<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\Instant;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\TimeZone;
use Brick\DateTime\ZonedDateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class InstantType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getDateTimeTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value) {
            return LocalDateTime::fromDateTime(new \DateTime($value))
                ->atTimeZone(TimeZone::utc())
                ->getInstant();
        }

        return null;
    }

    /**
     * @param Instant|null $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return ZonedDateTime::ofInstant($value, TimeZone::utc())
            ->toDateTime()
            ->format($platform->getDateTimeFormatString())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'instant';
    }
}
