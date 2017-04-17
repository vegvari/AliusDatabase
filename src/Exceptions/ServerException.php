<?php

namespace Alius\Database\Exceptions;

use Alius\Database\Interfaces;

class ServerException extends RuntimeException
{
    public static function databaseNotSet(string $server_class, string $database_class): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Database is not set in server "%s": "%s"', $server_class, $database_class), self::SERVER_DATABASE_NOT_SET);
    }
}
