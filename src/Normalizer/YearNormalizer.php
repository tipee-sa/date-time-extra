<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Year;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class YearNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param Year $object
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
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Year
    {
        try {
            return Year::of((int) $data);
        } catch (DateTimeException $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type), 0, $e);
        }
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof Year;
    }

    /**
     * @param class-string $type
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return Year::class === $type;
    }
}
