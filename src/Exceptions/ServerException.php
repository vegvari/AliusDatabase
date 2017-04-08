<?php

namespace Alius\Database\Exceptions;

class ServerException extends RuntimeException
{
    const SERVER_DATABASE_NOT_SET = 1201;

    public static function databaseNotSet(string $server_class, string $database_class): ExceptionInterface
    {
        throw new static(sprintf('Database is already set in server "%s": "%s"', $server_class, $database_class), self::SERVER_DATABASE_NOT_SET);
    }
}
