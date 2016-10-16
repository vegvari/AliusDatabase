<?php

namespace Alius\Database;

class ForeignKey extends Constraint
{
    const ACTIONS = ['CASCADE', 'NO ACTION', 'RESTRICT', 'SET DEFAULT', 'SET NULL'];

    protected $name;
    protected $parent_table;
    protected $parent_columns;
    protected $update_rule;
    protected $delete_rule;

    public function __construct(string $name, $columns, string $parent_table, $parent_columns = null, string $update_rule = 'RESTRICT', string $delete_rule = 'RESTRICT')
    {
        $columns = is_array($columns) ? $columns : [$columns];
        if (count($columns) !== count(array_unique($columns))) {
            throw new ConstraintException('Invalid foreign key, duplicated child column');
        }

        if ($parent_columns === null) {
            $parent_columns = $columns;
        } elseif (! is_array($parent_columns)) {
            $parent_columns = [$parent_columns];
        }

        if (count($parent_columns) !== count(array_unique($parent_columns))) {
            throw new ConstraintException('Invalid foreign key, duplicated parent column');
        }

        if (count($columns) > count($parent_columns)) {
            throw new ConstraintException('Invalid foreign key, more child columns than parent columns');
        } elseif (count($columns) < count($parent_columns)) {
            throw new ConstraintException('Invalid foreign key, more parent columns than child columns');
        }

        if (! in_array($update_rule, self::ACTIONS)) {
            throw new ConstraintException(sprintf('Invalid foreign key, on update action is not supported: "%s"', $update_rule));
        }

        if (! in_array($delete_rule, self::ACTIONS)) {
            throw new ConstraintException(sprintf('Invalid foreign key, on delete action is not supported: "%s"', $delete_rule));
        }

        $this->name = $name;
        $this->columns = $columns;
        $this->parent_table = $parent_table;
        $this->parent_columns = $parent_columns;
        $this->update_rule = $update_rule === 'NO ACTION' ? 'RESTRICT' : $update_rule;
        $this->delete_rule = $delete_rule === 'NO ACTION' ? 'RESTRICT' : $delete_rule;
    }

    public function getName(): string
    {
        return $this->name;
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
        return sprintf('CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)', $this->getName(), implode('`, `', $this->getColumns()), $this->getParentTable(), implode('`, `', $this->getParentColumns()));
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
