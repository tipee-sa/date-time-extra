<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\ZonedDateTime;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ZonedDateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param ZonedDateTime $object
     * @param array<mixed> $context
     */
    public function normalize($object, ?string $format = null, array $context = []): string
    {
        return (string) $object;
    }

    /**
     * @param mixed $data
     * @param class-string $type
     * @param array<mixed> $context
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): ZonedDateTime
    {
        try {
            return ZonedDateTime::parse($data);
        } catch (DateTimeException $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type));
        }
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof ZonedDateTime;
    }

    /**
     * @param mixed $data
     * @param class-string $type
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return ZonedDateTime::class === $type;
    }
}
