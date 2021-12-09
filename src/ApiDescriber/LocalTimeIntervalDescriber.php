<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Gammadia\DateTimeExtra\LocalTimeInterval;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

final class LocalTimeIntervalDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'local-time-interval';
        $schema->example = '12:34:56/PT2H';
    }

    public function supports(Model $model): bool
    {
        return LocalTimeInterval::class === $model->getType()->getClassName();
    }
}
