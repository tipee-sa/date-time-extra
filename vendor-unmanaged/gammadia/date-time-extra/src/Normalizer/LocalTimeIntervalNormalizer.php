<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Normalizer;

use Brick\DateTime\DateTimeException;
use Gammadia\DateTimeExtra\LocalTimeInterval;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocalTimeIntervalNormalizer // implements NormalizerInterface, DenormalizerInterface TODO: Remove comment -> temporary for fixing PHPStan errors
{
//    public function normalize($object, ?string $format = null, array $context = []): string TODO: Remove comment -> temporary for fixing PHPStan errors
//    {
//        return $object->toString();
//    }

    /**
     * @param mixed $data
     */
//    public function denormalize($data, string $type, ?string $format = null, array $context = []): LocalTimeInterval TODO: Remove comment -> temporary for fixing PHPStan errors
//    {
//        try {
//            return LocalTimeInterval::parse($data);
//        } catch (DateTimeException $e) {
//            throw new NotNormalizableValueException(sprintf('%s (%s)', $e->getMessage(), $type));
//        }
//    }

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