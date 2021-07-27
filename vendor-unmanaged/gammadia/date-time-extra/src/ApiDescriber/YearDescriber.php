<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Brick\DateTime\Year;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

final class YearDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        // We discussed having this one as an integer but opted for string for consistency with other date-related types
        $schema->type = 'string';
        $schema->format = 'year';
        $schema->example = '2021';
    }

    public function supports(Model $model): bool
    {
        return Year::class === $model->getType()->getClassName();
    }
}
