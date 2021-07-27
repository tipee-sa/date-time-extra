<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Brick\DateTime\Period;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

final class PeriodDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'period';
        $schema->example = 'P1Y2M3D';
    }

    public function supports(Model $model): bool
    {
        return Period::class === $model->getType()->getClassName();
    }
}
