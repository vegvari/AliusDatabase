<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

class ForeignKey extends Constraint implements Interfaces\ForeignKeyInterface
{
    const RULES = ['CASCADE', 'NO ACTION', 'RESTRICT', 'SET DEFAULT', 'SET NULL'];

    protected $parent_table;
    protected $parent_columns;
    protected $update_rule;
    protected $delete_rule;

    public function __construct(string $name, $columns, string $parent_table, $parent_columns = null, string $update_rule = 'RESTRICT', string $delete_rule = 'RESTRICT')
    {
        if ($name === '' || strtolower($name) === 'primary') {
            throw Exceptions\SchemaException::foreignKeyInvalidName($name);
        }

        $columns = is_array($columns) ? $columns : [$columns];

        if ($columns === []) {
            throw Exceptions\SchemaException::foreignKeyNoColumn($name);
        }

        $duplicated = array_unique(array_diff_key($columns, array_unique($columns)));
        if ($duplicated !== []) {
            throw Exceptions\SchemaException::foreignKeyDuplicatedChildColumn($name, ...$duplicated);
        }

        if ($parent_columns === null) {
            $parent_columns = $columns;
        } elseif (! is_array($parent_columns)) {
            $parent_columns = [$parent_columns];
        }

        $duplicated = array_unique(array_diff_key($parent_columns, array_unique($parent_columns)));
        if ($duplicated !== []) {
            throw Exceptions\SchemaException::foreignKeyDuplicatedParentColumn($name, ...$duplicated);
        }

        if (count($columns) > count($parent_columns)) {
            throw Exceptions\SchemaException::foreignKeyMoreChildColumn($name);
        } elseif (count($columns) < count($parent_columns)) {
            throw Exceptions\SchemaException::foreignKeyMoreParentColumn($name);
        }

        if (! in_array($update_rule, self::RULES)) {
            throw Exceptions\SchemaException::foreignKeyInvalidUpdateRule($name, $update_rule);
        }

        if (! in_array($delete_rule, self::RULES)) {
            throw Exceptions\SchemaException::foreignKeyInvalidDeleteRule($name, $delete_rule);
        }

        $this->name = $name;
        $this->columns = $columns;
        $this->parent_table = $parent_table;
        $this->parent_columns = $parent_columns;
        $this->update_rule = $update_rule === 'NO ACTION' ? 'RESTRICT' : $update_rule;
        $this->delete_rule = $delete_rule === 'NO ACTION' ? 'RESTRICT' : $delete_rule;
    }

    public function getParentTable(): string
    {
        return $this->parent_table;
    }

    public function getParentColumns(): array
    {
        return $this->parent_columns;
    }

    public function getUpdateRule(): string
    {
        return $this->update_rule;
    }

    public function getDeleteRule(): string
    {
        return $this->delete_rule;
    }

    public function buildCreate(): string
    {
        return sprintf('CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON UPDATE %s ON DELETE %s',
            $this->getName(),
            implode('`, `', $this->getColumns()),
            $this->getParentTable(),
            implode('`, `', $this->getParentColumns()),
            $this->getUpdateRule(),
            $this->getDeleteRule()
        );
    }

    public function buildAdd(): string
    {
        return sprintf('ADD %s', $this->buildCreate());
    }

    public function buildDrop(): string
    {
        return sprintf('DROP FOREIGN KEY `%s`', $this->getName());
    }
}
