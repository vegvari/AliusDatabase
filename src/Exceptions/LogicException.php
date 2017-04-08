<?php

namespace Alius\Database\Exceptions;

use Alius\Database\Interfaces\ExceptionInterface;

class LogicException extends \LogicException implements ExceptionInterface
{
    const IMMUTABLE        = 1;
    const INVALID_DATABASE = 2;
    const INVALID_TABLE    = 3;

    public static function immutable(string $class): ExceptionInterface
    {
        return new static(sprintf('Class "%s" is immutable', $class), self::IMMUTABLE);
    }

    public static function invalidDatabase(string $interface, string $class): ExceptionInterface
    {
        throw new static(sprintf('Database must implement interface "%s": "%s"', $interface, $class), self::INVALID_DATABASE);
    }

    public static function invalidTable(string $interface, string $class): ExceptionInterface
    {
        throw new static(sprintf('Table must implement interface "%s": "%s"', $interface, $class), self::INVALID_TABLE);
    }
}
