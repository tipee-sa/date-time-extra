<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Brick\DateTime\MonthDay;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

final class MonthDayDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'month-day';
        $schema->example = '--12-31';
    }

    public function supports(Model $model): bool
    {
        return MonthDay::class === $model->getType()->getClassName();
    }
}
