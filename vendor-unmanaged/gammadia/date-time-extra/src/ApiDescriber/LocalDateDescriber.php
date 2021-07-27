<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Brick\DateTime\LocalDate;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

final class LocalDateDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'local-date';
        $schema->example = '2019-11-11';
    }

    public function supports(Model $model): bool
    {
        return LocalDate::class === $model->getType()->getClassName();
    }
}
