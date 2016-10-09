<?php

namespace Alius\Database;

class ForeignKey
{
    const ACTIONS = ['CASCADE', 'NO ACTION', 'RESTRICT', 'SET DEFAULT', 'SET NULL'];

    protected $name;
    protected $columns;
    protected $parent_table;
    protected $parent_columns;
    protected $on_update;
    protected $on_delete;

    public function __construct(string $name, $columns, string $parent_table, $parent_columns = null, string $on_update = 'RESTRICT', string $on_delete = 'RESTRICT')
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

        if (! in_array($on_update, self::ACTIONS)) {
            throw new ConstraintException(sprintf('Invalid foreign key, on update action is not supported: "%s"', $on_update));
        }

        if (! in_array($on_delete, self::ACTIONS)) {
            throw new ConstraintException(sprintf('Invalid foreign key, on delete action is not supported: "%s"', $on_delete));
        }

        $this->name = $name;
        $this->columns = $columns;
        $this->parent_table = $parent_table;
        $this->parent_columns = $parent_columns;
        $this->on_update = $on_update === 'NO ACTION' ? 'RESTRICT' : $on_update;
        $this->on_delete = $on_delete === 'NO ACTION' ? 'RESTRICT' : $on_delete;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getParentTable(): string
    {
        return $this->parent_table;
    }

    public function getParentColumns(): array
    {
        return $this->parent_columns;
    }

    public function getOnUpdateAction(): string
    {
        return $this->on_update;
    }

    public function getOnDeleteAction(): string
    {
        return $this->on_delete;
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
