<?php

namespace Alius\Database\Exceptions;

class TableException extends RuntimeException
{
    public static function columnNotSet(string $table_class, string $column_name): ExceptionInterface
    {
        throw new static(sprintf('Column is not set in table "%s": "%s"', $table_class, $column_name), self::TABLE_COLUMN_NOT_SET);
    }

    public static function primaryKeyNotSet(string $table_class): ExceptionInterface
    {
        throw new static(sprintf('Primary key is not set in table "%s"', $table_class), self::TABLE_PRIMARY_KEY_NOT_SET);
    }

    public static function uniqueKeyNotSet(string $table_class, string $unique_key_name): ExceptionInterface
    {
        throw new static(sprintf('Unique key "%s" is not set in table "%s"', $unique_key_name, $table_class), self::TABLE_UNIQUE_KEY_NOT_SET);
    }

    public static function indexNotSet(string $table_class, string $index_name): ExceptionInterface
    {
        throw new static(sprintf('Index "%s" is not set in table "%s"', $index_name, $table_class), self::TABLE_INDEX_NOT_SET);
    }

    public static function foreignKeyNotSet(string $table_class, string $foreign_key_name): ExceptionInterface
    {
        throw new static(sprintf('Foreign key "%s" is not set in table "%s"', $foreign_key_name, $table_class), self::TABLE_FOREIGN_KEY_NOT_SET);
    }
}
