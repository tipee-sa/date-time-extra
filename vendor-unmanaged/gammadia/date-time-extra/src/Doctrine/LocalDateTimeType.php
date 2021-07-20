<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Parser\PatternParserBuilder;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class LocalDateTimeType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTimeTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?LocalDateTime
    {
        if ($value) {
            $parser = (new PatternParserBuilder())
                ->append(IsoParsers::localDate())
                ->appendLiteral(' ')
                ->append(IsoParsers::localTime())
                ->toParser();

            return LocalDateTime::parse($value, $parser);
        }

        return null;
    }

    public function getName(): string
    {
        return 'local_datetime';
    }
}
