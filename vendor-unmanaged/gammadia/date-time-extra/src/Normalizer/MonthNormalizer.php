<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\Month;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function Gammadia\Collections\Functional\mapWithKeys;

final class MonthNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var array<string, Month>
     */
    private array $months;

    public function __construct()
    {
        $this->months = mapWithKeys(Month::getAll(), fn (Month $Month): iterable
            => yield $this->normalize($Month) => $Month
        );
    }

    /**
     * @param Month $object
     * @param mixed[] $context
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        return strtolower((string) $object);
    }

    /**
     * @param class-string $type
     * @param mixed[] $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Month
    {
        return $this->months[(string) $data] ?? throw new NotNormalizableValueException(sprintf('The value "%s" is not a valid month.', $data));
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof Month;
    }

    /**
     * @param class-string $type
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return Month::class === $type;
    }
}
