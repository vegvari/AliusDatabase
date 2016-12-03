<?php

namespace Alius\Database;

class DatabaseException extends RuntimeException
{
    const DATABASE_TABLE_NOT_SET = 2301;

    public static function tableNotSet(string $database_class, string $table_name): ExceptionInterface
    {
        throw new static(sprintf('Table is not set in database "%s": "%s"', $database_class, $table_name), self::DATABASE_TABLE_NOT_SET);
    }
}
