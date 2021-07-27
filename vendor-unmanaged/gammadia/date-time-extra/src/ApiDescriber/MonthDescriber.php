<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\ApiDescriber;

use Brick\DateTime\Month;
use Gammadia\DateTimeExtra\Normalizer\MonthNormalizer;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;
use function Gammadia\Collections\Functional\first;
use function Gammadia\Collections\Functional\map;

final class MonthDescriber implements ModelDescriberInterface
{
    public function __construct(
        private MonthNormalizer $normalizer,
    ) {}

    public function describe(Model $model, Schema $schema): void
    {
        $schema->type = 'string';
        $schema->format = 'month';

        $values = map(Month::getAll(), fn (Month $month): string => $this->normalizer->normalize($month));

        $schema->example = first($values);
        $schema->enum = $values;
    }

    public function supports(Model $model): bool
    {
        return Month::class === $model->getType()->getClassName();
    }
}
