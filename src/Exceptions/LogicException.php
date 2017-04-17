<?php

namespace Alius\Database\Exceptions;

use Alius\Database\Interfaces;

class LogicException extends \LogicException implements Interfaces\ExceptionInterface
{
    public static function immutable(string $class): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Class "%s" is immutable', $class), self::IMMUTABLE);
    }

    public static function invalidDatabase(string $interface, string $class): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Database must implement interface "%s": "%s"', $interface, $class), self::INVALID_DATABASE);
    }

    public static function invalidTable(string $interface, string $class): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Table must implement interface "%s": "%s"', $interface, $class), self::INVALID_TABLE);
    }
}
