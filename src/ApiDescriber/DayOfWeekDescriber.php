<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Brick\DateTime\DayOfWeek;
use Gammadia\DateTimeExtra\Normalizer\DayOfWeekNormalizer;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;
use function Gammadia\Collections\Functional\first;
use function Gammadia\Collections\Functional\map;

final class DayOfWeekDescriber implements ModelDescriberInterface
{
    public function __construct(
        private DayOfWeekNormalizer $normalizer,
    ) {}

    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'day-of-week';

        $values = map(DayOfWeek::all(), fn (DayOfWeek $dayOfWeek): string => $this->normalizer->normalize($dayOfWeek));

        $schema->example = first($values);
        $schema->enum = $values;
    }

    public function supports(Model $model): bool
    {
        return DayOfWeek::class === $model->getType()->getClassName();
    }
}
