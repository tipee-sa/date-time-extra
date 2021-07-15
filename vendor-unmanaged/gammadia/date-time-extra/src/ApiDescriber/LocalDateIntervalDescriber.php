<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Gammadia\DateTimeExtra\LocalDateInterval;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

final class LocalDateIntervalDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'local-date-interval';
        $schema->example = '2019-11-11/2019-12-12';
    }

    public function supports(Model $model): bool
    {
        return LocalDateInterval::class === $model->getType()->getClassName();
    }
}
