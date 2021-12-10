<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Gammadia\DateTimeExtra\LocalTimeInterval;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

final class LocalTimeIntervalNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param LocalTimeInterval $object
     * @param mixed[] $context
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        return $object->toString();
    }

    /**
     * @param class-string $type
     * @param mixed[] $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): LocalTimeInterval
    {
        try {
            return LocalTimeInterval::parse($data);
        } catch (Throwable $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type), 0, $e);
        }
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof LocalTimeInterval;
    }

    /**
     * @param class-string $type
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return LocalTimeInterval::class === $type;
    }
}
