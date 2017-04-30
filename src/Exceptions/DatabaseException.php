<?php

namespace Alius\Database\Exceptions;

class DatabaseException extends RuntimeException
{
    public static function tableNotSet(string $database_class, string $table_name): ExceptionInterface
    {
        throw new static(sprintf('Table is not set in database "%s (%s)": "%s"', $database_class::getName(), $database_class, $table_name), self::DATABASE_TABLE_NOT_SET);
    }
}
