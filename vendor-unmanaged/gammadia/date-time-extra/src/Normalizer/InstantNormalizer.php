<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\ZonedDateTime;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InstantNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param Instant $object
     * @param mixed[] $context
     */
    public function normalize($object, ?string $format = null, array $context = []): string
    {
        return (string) $object;
    }

    /**
     * @param mixed $data
     * @param class-string $type
     * @param mixed[] $context
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): Instant
    {
        try {
            return ZonedDateTime::parse($data)->getInstant();
        } catch (DateTimeException $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type));
        }
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof Instant;
    }

    /**
     * @param mixed $data
     * @param class-string $type
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return Instant::class === $type;
    }
}
