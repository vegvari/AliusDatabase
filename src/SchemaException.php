<?php

namespace Alius\Database;

final class SchemaException extends LogicException implements ExceptionInterface
{
    // server
    const SERVER_DATABASE_ALREADY_SET          = 1000;
    const SERVER_DATABASE_NOT_SET              = 1200;

    // db
    const DATABASE_INVALID_NAME                = 2000;
    const DATABASE_TABLE_ALREADY_SET           = 2300;

    // table
    const TABLE_INVALID_NAME                   = 3000;
    const TABLE_COLUMN_ALREADY_SET             = 3400;
    const TABLE_PRIMARY_KEY_ALREADY_SET        = 3500;
    const TABLE_UNIQUE_KEY_ALREADY_SET         = 3600;
    const TABLE_INDEX_ALREADY_SET              = 3700;
    const TABLE_FOREIGN_KEY_ALREADY_SET        = 3800;

    // column
    const COLUMN_INVALID_NAME                  = 4000;

    // primary key
    const PRIMARY_KEY_NO_COLUMN                = 5000;
    const PRIMARY_KEY_DUPLICATED_COLUMN        = 5001;

    // unique key
    const UNIQUE_KEY_INVALID_NAME              = 6000;
    const UNIQUE_KEY_NO_COLUMN                 = 6001;
    const UNIQUE_KEY_DUPLICATED_COLUMN         = 6002;

    // index
    const INDEX_INVALID_NAME                   = 7000;
    const INDEX_NO_COLUMN                      = 7001;
    const INDEX_DUPLICATED_COLUMN              = 7002;

    // foreign key
    const FOREIGN_KEY_INVALID_NAME             = 8000;
    const FOREIGN_KEY_NO_COLUMN                = 8001;
    const FOREIGN_KEY_DUPLICATED_CHILD_COLUMN  = 8002;
    const FOREIGN_KEY_DUPLICATED_PARENT_COLUMN = 8003;
    const FOREIGN_KEY_MORE_CHILD_COLUMN        = 8004;
    const FOREIGN_KEY_MORE_PARENT_COLUMN       = 8005;
    const FOREIGN_KEY_INVALID_UPDATE_RULE      = 8006;
    const FOREIGN_KEY_INVALID_DELETE_RULE      = 8007;

    public static function databaseAlreadySet(string $server_class, string $database_class): ExceptionInterface
    {
        throw new static(sprintf('Database is already set in server "%s": "%s"', $server_class, $database_class), self::SERVER_DATABASE_ALREADY_SET);
    }

    public static function databaseNotSet(string $server_class, string $database_name): ExceptionInterface
    {
        throw new static(sprintf('Database is not set in server "%s": "%s"', $server_class, $database_name), self::SERVER_DATABASE_NOT_SET);
    }

    public static function invalidDatabaseName(string $class): ExceptionInterface
    {
        return new static(sprintf('Invalid database name, set the NAME constant in class: "%s"', $class), self::DATABASE_INVALID_NAME);
    }

    public static function tableAlreadySet(string $database_class, string $table_class): ExceptionInterface
    {
        throw new static(sprintf('Table is already set in database "%s": "%s"', $database_class, $table_class), self::DATABASE_TABLE_ALREADY_SET);
    }

    public static function invalidTableName(string $class): ExceptionInterface
    {
        return new static(sprintf('Invalid table name, set the NAME constant in class: "%s"', $class), self::TABLE_INVALID_NAME);
    }

    public static function columnAlreadySet(string $table_class, string $column_name): ExceptionInterface
    {
        throw new static(sprintf('Column is already set in table "%s": "%s"', $table_class, $column_name), self::TABLE_COLUMN_ALREADY_SET);
    }

    public static function primaryKeyAlreadySet(string $table_class): ExceptionInterface
    {
        throw new static(sprintf('Primary key is already set in table "%s"', $table_class), self::TABLE_PRIMARY_KEY_ALREADY_SET);
    }

    public static function uniqueKeyAlreadySet(string $table_class, string $unique_key_name): ExceptionInterface
    {
        throw new static(sprintf('Unique key "%s" is already set in table "%s"', $unique_key_name, $table_class), self::TABLE_UNIQUE_KEY_ALREADY_SET);
    }

    public static function indexAlreadySet(string $table_class, string $index_name): ExceptionInterface
    {
        throw new static(sprintf('Index "%s" is already set in table "%s"', $index_name, $table_class), self::TABLE_INDEX_ALREADY_SET);
    }

    public static function foreignKeyAlreadySet(string $table_class, string $foreign_key_name): ExceptionInterface
    {
        throw new static(sprintf('Foreign key "%s" is already set in table "%s"', $foreign_key_name, $table_class), self::TABLE_FOREIGN_KEY_ALREADY_SET);
    }

    public static function primaryKeyNoColumn(): ExceptionInterface
    {
        return new static('Invalid primary key, no column', self::PRIMARY_KEY_NO_COLUMN);
    }

    public static function primaryKeyDuplicatedColumn(string ...$columns): ExceptionInterface
    {
        return new static(sprintf('Duplicated column in primary key: %s', implode(', ', $columns)), self::PRIMARY_KEY_DUPLICATED_COLUMN);
    }

    public static function uniqueKeyInvalidName(string $name): ExceptionInterface
    {
        return new static(sprintf('Invalid unique key name: "%s"', $name), self::UNIQUE_KEY_INVALID_NAME);
    }

    public static function uniqueKeyNoColumn(string $name): ExceptionInterface
    {
        return new static(sprintf('Invalid unique key, no column: "%s"', $name), self::UNIQUE_KEY_NO_COLUMN);
    }

    public static function uniqueKeyDuplicatedColumn(string $name, string ...$columns): ExceptionInterface
    {
        return new static(sprintf('Duplicated column in unique key "%s": %s', $name, implode(', ', $columns)), self::UNIQUE_KEY_DUPLICATED_COLUMN);
    }

    public static function indexInvalidName(string $name): ExceptionInterface
    {
        return new static(sprintf('Invalid index name: "%s"', $name), self::INDEX_INVALID_NAME);
    }

    public static function indexNoColumn(string $name): ExceptionInterface
    {
        return new static(sprintf('Invalid index, no column: "%s"', $name), self::INDEX_NO_COLUMN);
    }

    public static function indexDuplicatedColumn(string $name, string ...$columns): ExceptionInterface
    {
        return new static(sprintf('Duplicated column in index "%s": %s', $name, implode(', ', $columns)), self::INDEX_DUPLICATED_COLUMN);
    }

    public static function foreignKeyInvalidName(string $name): ExceptionInterface
    {
        return new static(sprintf('Invalid foreign key name: "%s"', $name), self::FOREIGN_KEY_INVALID_NAME);
    }

    public static function foreignKeyNoColumn(string $name): ExceptionInterface
    {
        return new static(sprintf('Invalid foreign key, no column: "%s"', $name), self::FOREIGN_KEY_NO_COLUMN);
    }

    public static function foreignKeyDuplicatedChildColumn(string $name, string ...$columns): ExceptionInterface
    {
        return new static(sprintf('Duplicated child column in foreign key "%s": %s', $name, implode(', ', $columns)), self::FOREIGN_KEY_DUPLICATED_CHILD_COLUMN);
    }

    public static function foreignKeyDuplicatedParentColumn(string $name, string ...$columns): ExceptionInterface
    {
        return new static(sprintf('Duplicated parent column in foreign key "%s": %s', $name, implode(', ', $columns)), self::FOREIGN_KEY_DUPLICATED_PARENT_COLUMN);
    }

    public static function foreignKeyMoreChildColumn(string $name): ExceptionInterface
    {
        return new static(sprintf('More child column than parent column in foreign key "%s"', $name), self::FOREIGN_KEY_MORE_CHILD_COLUMN);
    }

    public static function foreignKeyMoreParentColumn(string $name): ExceptionInterface
    {
        return new static(sprintf('More child parent than child column in foreign key "%s"', $name), self::FOREIGN_KEY_MORE_PARENT_COLUMN);
    }

    public static function foreignKeyInvalidUpdateRule(string $name, string $update_rule): ExceptionInterface
    {
        return new static(sprintf('Invalid update action in foreign key "%s": %s', $name, $update_rule), self::FOREIGN_KEY_INVALID_UPDATE_RULE);
    }

    public static function foreignKeyInvalidDeleteRule(string $name, string $delete_rule): ExceptionInterface
    {
        return new static(sprintf('Invalid delete action in foreign key "%s": %s', $name, $delete_rule), self::FOREIGN_KEY_INVALID_DELETE_RULE);
    }
}