<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DateTimeException;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocalDateTimeIntervalNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param LocalDateTimeInterval $object
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
    public function denormalize($data, string $type, ?string $format = null, array $context = []): LocalDateTimeInterval
    {
        try {
            return LocalDateTimeInterval::parse($data);
        } catch (DateTimeException $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type));
        }
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof LocalDateTimeInterval;
    }

    /**
     * @param mixed $data
     * @param class-string $type
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return LocalDateTimeInterval::class === $type;
    }
}
