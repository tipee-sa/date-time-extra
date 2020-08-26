<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\JMS;

use forum\brain\http\HttpStatus;
use Gammadia\DateTimeExtra\IntervalParseException;
use Gammadia\DateTimeExtra\LocalDateTimeInterval;
use Gammadia\Moment\Period;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use RuntimeException;
use Throwable;

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
                'method' => 'serializeLocalDateTimeIntervalToJson',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => LocalDateTimeInterval::class,
                'method' => 'deserializeLocalDateTimeIntervalFromJson',
            ],
        ];
    }

    public function serializeLocalDateTimeIntervalToJson(
        JsonSerializationVisitor $visitor,
        LocalDateTimeInterval $interval
    ): string {
        return $interval->toString();
    }

    /**
     * @param mixed $interval
     */
    public function deserializeLocalDateTimeIntervalFromJson(
        JsonDeserializationVisitor $visitor,
        $interval
    ): LocalDateTimeInterval {
        try {
            return LocalDateTimeInterval::parse($interval);
        } catch (IntervalParseException|RuntimeException $throwable) {
            throw new RuntimeException(
                sprintf('Invalid interval: "%s"', $interval),
                HttpStatus::HTTP_BAD_REQUEST,
                $throwable
            );
        }
    }
}
