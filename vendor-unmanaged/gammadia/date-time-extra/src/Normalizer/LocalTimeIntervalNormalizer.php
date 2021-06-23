<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Gammadia\DateTimeExtra\LocalTimeInterval;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

class LocalTimeIntervalNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param LocalTimeInterval $object
     * @param mixed[] $context
     */
    public function normalize($object, ?string $format = null, array $context = []): string
    {
        return $object->toString();
    }

    /**
     * @param mixed $data
     * @param class-string $type
     * @param mixed[] $context
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): LocalTimeInterval
    {
        try {
            return LocalTimeInterval::parse($data);
        } catch (Throwable $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type));
        }
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof LocalTimeInterval;
    }

    /**
     * @param mixed $data
     * @param class-string $type
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return LocalTimeInterval::class === $type;
    }
}
