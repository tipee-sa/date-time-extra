<?php

declare(strict_types=1);

namespace Gammadia\DateTimeExtra\Exceptions\Assert;

use Gammadia\DateTimeExtra\Exceptions\InvalidArgumentException;

class Assert extends \Webmozart\Assert\Assert
{
    protected static function reportInvalidArgument($message)
    {
        throw new InvalidArgumentException($message);
    }
}
