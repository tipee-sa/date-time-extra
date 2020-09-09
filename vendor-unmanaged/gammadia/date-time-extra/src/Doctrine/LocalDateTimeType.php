<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Doctrine;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Parser\PatternParserBuilder;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LocalDateTimeType extends Type
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
            $parser = (new PatternParserBuilder())
                ->append(IsoParsers::localDate())
                ->appendLiteral(' ')
                ->append(IsoParsers::localTime())
                ->toParser();

            return LocalDateTime::parse($value, $parser);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'local_datetime';
    }
}
