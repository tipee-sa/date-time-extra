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
     * @param Duration|null $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        return null !== $value
            ? (int) round($value->toMillis() / LocalTime::MILLIS_PER_SECOND, 0, PHP_ROUND_HALF_EVEN)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return null !== $value
            ? Duration::ofSeconds($value)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'duration';
    }
}
