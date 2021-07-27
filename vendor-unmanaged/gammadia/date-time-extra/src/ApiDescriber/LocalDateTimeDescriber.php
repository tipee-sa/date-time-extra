<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Brick\DateTime\LocalDateTime;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

final class LocalDateTimeDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'local-date-time';
        $schema->example = '2019-11-11T12:34:56';
    }

    public function supports(Model $model): bool
    {
        return LocalDateTime::class === $model->getType()->getClassName();
    }
}
