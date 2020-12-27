<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DurationType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getDateTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param Duration $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): int
    {
        return (int) round($value->toMillis() / LocalTime::MILLIS_PER_SECOND, 0, PHP_ROUND_HALF_EVEN);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return Duration::ofSeconds($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'duration';
    }
}
