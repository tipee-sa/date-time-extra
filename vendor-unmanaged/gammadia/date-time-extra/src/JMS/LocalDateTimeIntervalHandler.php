<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\JMS;

use forum\brain\http\HttpStatus;
use Gammadia\DateTimeExtra\Exceptions\IntervalParseException;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use RuntimeException;

/**
 * @deprecated We should do everything we can NOT to implement normalizers for the legacy !
 */
final class LocalDateTimeIntervalHandler implements SubscribingHandlerInterface
{
    /**
     * @return mixed[]
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => LocalDateTimeInterval::class,
                'method' => 'serializeToJson',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => LocalDateTimeInterval::class,
                'method' => 'deserializeFromJson',
            ],
        ];
    }

    public function serializeToJson(JsonSerializationVisitor $visitor, ?LocalDateTimeInterval $timeRange): ?string
    {
        return null !== $timeRange ? (string) $timeRange : null;
    }

    public function deserializeFromJson(JsonDeserializationVisitor $visitor, null|string $timeRange): ?LocalDateTimeInterval
    {
        if (null === $timeRange) {
            return null;
        }

        try {
            return LocalDateTimeInterval::parse($timeRange);
        } catch (IntervalParseException | RuntimeException $throwable) {
            throw new RuntimeException(
                sprintf('Invalid time range: "%s"', $timeRange),
                HttpStatus::HTTP_BAD_REQUEST,
                $throwable,
            );
        }
    }
}
