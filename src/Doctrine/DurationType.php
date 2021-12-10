<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\Duration;
use Brick\DateTime\LocalTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class DurationType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTypeDeclarationSQL($column);
    }

    /**
     * @param Duration|null $value
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        return null !== $value
            ? (int) round($value->toMillis() / LocalTime::MILLIS_PER_SECOND, 0, PHP_ROUND_HALF_EVEN)
            : null;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Duration
    {
        return null !== $value
            ? Duration::ofSeconds((int) $value)
            : null;
    }

    public function getName(): string
    {
        return 'duration';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
