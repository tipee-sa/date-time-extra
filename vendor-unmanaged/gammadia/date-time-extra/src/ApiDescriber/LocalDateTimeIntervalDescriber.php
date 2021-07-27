<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

final class LocalDateTimeIntervalDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'local-date-time-interval';
        $schema->example = '2019-11-11T12:34:56/2019-12-12T23:59:59';
    }

    public function supports(Model $model): bool
    {
        return LocalDateTimeInterval::class === $model->getType()->getClassName();
    }
}
