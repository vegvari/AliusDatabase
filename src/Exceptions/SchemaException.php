<?php

namespace Alius\Database\Exceptions;

use Alius\Database\Interfaces;

final class SchemaException extends LogicException implements Interfaces\ExceptionInterface
{
    public static function serverAlreadySet(string $server_name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Server is already set in container: "%s"', $server_name), self::CONTAINER_SERVER_ALREADY_SET);
    }

    public static function invalidServerName(string $class): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid server name, set the $name static variable in class: "%s"', $class), self::SERVER_INVALID_NAME);
    }

    public static function databaseAlreadySet(string $server_class, string $database_class): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Database is already set in server "%s": "%s"', $server_class, $database_class), self::SERVER_DATABASE_ALREADY_SET);
    }

    public static function invalidDatabaseName(string $class): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid database name, set the $name static variable in class: "%s"', $class), self::DATABASE_INVALID_NAME);
    }

    public static function tableAlreadySet(string $database_class, string $table_class): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Table is already set in database "%s": "%s"', $database_class, $table_class), self::DATABASE_TABLE_ALREADY_SET);
    }

    public static function invalidTableName(string $class): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid table name, set the $name static variable in class: "%s"', $class), self::TABLE_INVALID_NAME);
    }

    public static function columnAlreadySet(string $table_class, string $column_name): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Column is already set in table "%s": "%s"', $table_class, $column_name), self::TABLE_COLUMN_ALREADY_SET);
    }

    public static function primaryKeyAlreadySet(string $table_class): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Primary key is already set in table "%s"', $table_class), self::TABLE_PRIMARY_KEY_ALREADY_SET);
    }

    public static function uniqueKeyAlreadySet(string $table_class, string $unique_key_name): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Unique key "%s" is already set in table "%s"', $unique_key_name, $table_class), self::TABLE_UNIQUE_KEY_ALREADY_SET);
    }

    public static function indexAlreadySet(string $table_class, string $index_name): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Index "%s" is already set in table "%s"', $index_name, $table_class), self::TABLE_INDEX_ALREADY_SET);
    }

    public static function foreignKeyAlreadySet(string $table_class, string $foreign_key_name): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Foreign key "%s" is already set in table "%s"', $foreign_key_name, $table_class), self::TABLE_FOREIGN_KEY_ALREADY_SET);
    }

    public static function primaryKeyNoColumn(): Interfaces\ExceptionInterface
    {
        return new static('Invalid primary key, no column', self::PRIMARY_KEY_NO_COLUMN);
    }

    public static function primaryKeyDuplicatedColumn(string ...$columns): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Duplicated column in primary key: %s', implode(', ', $columns)), self::PRIMARY_KEY_DUPLICATED_COLUMN);
    }

    public static function uniqueKeyInvalidName(string $name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid unique key name: "%s"', $name), self::UNIQUE_KEY_INVALID_NAME);
    }

    public static function uniqueKeyNoColumn(string $name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid unique key, no column: "%s"', $name), self::UNIQUE_KEY_NO_COLUMN);
    }

    public static function uniqueKeyDuplicatedColumn(string $name, string ...$columns): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Duplicated column in unique key "%s": %s', $name, implode(', ', $columns)), self::UNIQUE_KEY_DUPLICATED_COLUMN);
    }

    public static function indexInvalidName(string $name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid index name: "%s"', $name), self::INDEX_INVALID_NAME);
    }

    public static function indexNoColumn(string $name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid index, no column: "%s"', $name), self::INDEX_NO_COLUMN);
    }

    public static function indexDuplicatedColumn(string $name, string ...$columns): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Duplicated column in index "%s": %s', $name, implode(', ', $columns)), self::INDEX_DUPLICATED_COLUMN);
    }

    public static function foreignKeyInvalidName(string $name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid foreign key name: "%s"', $name), self::FOREIGN_KEY_INVALID_NAME);
    }

    public static function foreignKeyNoColumn(string $name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid foreign key, no column: "%s"', $name), self::FOREIGN_KEY_NO_COLUMN);
    }

    public static function foreignKeyDuplicatedChildColumn(string $name, string ...$columns): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Duplicated child column in foreign key "%s": %s', $name, implode(', ', $columns)), self::FOREIGN_KEY_DUPLICATED_CHILD_COLUMN);
    }

    public static function foreignKeyDuplicatedParentColumn(string $name, string ...$columns): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Duplicated parent column in foreign key "%s": %s', $name, implode(', ', $columns)), self::FOREIGN_KEY_DUPLICATED_PARENT_COLUMN);
    }

    public static function foreignKeyMoreChildColumn(string $name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('More child column than parent column in foreign key "%s"', $name), self::FOREIGN_KEY_MORE_CHILD_COLUMN);
    }

    public static function foreignKeyMoreParentColumn(string $name): Interfaces\ExceptionInterface
    {
        return new static(sprintf('More child parent than child column in foreign key "%s"', $name), self::FOREIGN_KEY_MORE_PARENT_COLUMN);
    }

    public static function foreignKeyInvalidUpdateRule(string $name, string $update_rule): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid update action in foreign key "%s": %s', $name, $update_rule), self::FOREIGN_KEY_INVALID_UPDATE_RULE);
    }

    public static function foreignKeyInvalidDeleteRule(string $name, string $delete_rule): Interfaces\ExceptionInterface
    {
        return new static(sprintf('Invalid delete action in foreign key "%s": %s', $name, $delete_rule), self::FOREIGN_KEY_INVALID_DELETE_RULE);
    }

    public static function invalidColumnType(string $type)
    {
        return new static(sprintf('Invalid type: "%s"', $type), self::COLUMN_INVALID_TYPE);
    }

    public static function invalidColumnIntAutoIncrementNullable()
    {
        return new static('Auto increment column can\'t be nullable', self::COLUMN_INT_INVALID_AUTO_INCREMENT_NULLABLE);
    }

    public static function invalidColumnIntAutoIncrementDefault()
    {
        return new static('Auto increment column can\'t have default value', self::COLUMN_INT_INVALID_AUTO_INCREMENT_DEFAULT);
    }

    public static function invalidColumnIntValueMin(int $min)
    {
        return new static(sprintf('Value must be greater than %d', $min), self::COLUMN_INT_INVALID_VALUE_MIN);
    }

    public static function invalidColumnIntValueMax(int $max)
    {
        return new static(sprintf('Value must be less than %d', $max), self::COLUMN_INT_INVALID_VALUE_MAX);
    }

    public static function invalidColumnFloatPrecision(int $precision)
    {
        return new static(sprintf('Invalid precision: "%s", it must be 1-65', $precision), self::COLUMN_FLOAT_INVALID_PRECISION);
    }

    public static function invalidColumnFloatScale(int $scale)
    {
        return new static(sprintf('Invalid scale: "%s", it must be 0-30', $scale), self::COLUMN_FLOAT_INVALID_SCALE);
    }

    public static function invalidColumnFloatScaleMax(int $scale, int $precision)
    {
        return new static(sprintf('Invalid scale: "%s", it must be less than precision: "%s"', $scale, $precision), self::COLUMN_FLOAT_INVALID_SCALE_MAX);
    }

    public static function invalidColumnFloatValue()
    {
        return new static('Invalid float value', self::COLUMN_FLOAT_INVALID_VALUE);
    }

    public static function invalidColumnFloatValueMin(int $min)
    {
        return new static(sprintf('Value must be greater than %d', $min), self::COLUMN_FLOAT_INVALID_VALUE_MIN);
    }

    public static function invalidColumnFloatValueMax(int $max)
    {
        return new static(sprintf('Value must be less than %d', $max), self::COLUMN_FLOAT_INVALID_VALUE_MAX);
    }

    public static function invalidColumnStringLength(string $type)
    {
        return new static(sprintf('Value is too long for %s', $type), self::COLUMN_STRING_INVALID_LENGTH);
    }
}
