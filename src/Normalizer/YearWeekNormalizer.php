<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\YearWeek;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;
use function Gammadia\Collections\Functional\map;

final class YearWeekNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param YearWeek $object
     * @param mixed[] $context
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        return (string) $object;
    }

    /**
     * @param class-string $type
     * @param mixed[] $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): YearWeek
    {
        try {
            return YearWeek::of(...map((array) explode('-W', (string) $data, 2), static fn (string $part): int => (int) $part));
        } catch (DateTimeException $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type), 0, $e);
        } catch (Throwable $e) {
            throw new NotNormalizableValueException('Invalid format for year week.', 0, $e);
        }
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof YearWeek;
    }

    /**
     * @param class-string $type
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return YearWeek::class === $type;
    }
}
