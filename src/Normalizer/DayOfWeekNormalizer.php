<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DayOfWeek;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function Gammadia\Collections\Functional\collectWithKeys;

final class DayOfWeekNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var array<string, DayOfWeek>
     */
    private array $daysOfWeek;

    public function __construct()
    {
        $this->daysOfWeek = collectWithKeys(DayOfWeek::all(), fn (DayOfWeek $dayOfWeek): iterable
            => yield $this->normalize($dayOfWeek) => $dayOfWeek,
        );
    }

    /**
     * @param DayOfWeek $object
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
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): DayOfWeek
    {
        return $this->daysOfWeek[(string) $data] ?? throw new NotNormalizableValueException(
            sprintf('The value "%s" is not a valid day of week.', $data),
        );
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof DayOfWeek;
    }

    /**
     * @param class-string $type
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return DayOfWeek::class === $type;
    }
}
