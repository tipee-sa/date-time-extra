<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\TimeZone;
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
            return LocalDateTime::parse(str_replace(' ', 'T', $value))
                ->atTimeZone(TimeZone::utc())
                ->getInstant();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'instant';
    }
}
