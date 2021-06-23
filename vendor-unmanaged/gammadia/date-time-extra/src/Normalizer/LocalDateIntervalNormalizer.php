<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DateTimeException;
use Gammadia\DateTimeExtra\LocalDateInterval;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocalDateIntervalNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param LocalDateInterval $object
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
    public function denormalize($data, string $type, ?string $format = null, array $context = []): LocalDateInterval
    {
        try {
            return LocalDateInterval::parse($data);
        } catch (DateTimeException $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type));
        }
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof LocalDateInterval;
    }

    /**
     * @param mixed $data
     * @param class-string $type
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return LocalDateInterval::class === $type;
    }
}
