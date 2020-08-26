<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\LocalDate;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocalDateNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param LocalDate $object
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
    public function denormalize($data, string $type, ?string $format = null, array $context = []): LocalDate
    {
        try {
            return LocalDate::parse($data);
        } catch (DateTimeException $e) {
            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type));
        }
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof LocalDate;
    }

    /**
     * @param mixed $data
     * @param class-string $type
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return LocalDate::class === $type;
    }
}
