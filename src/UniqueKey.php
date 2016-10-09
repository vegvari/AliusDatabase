<?php

namespace Alius\Database;

class UniqueKey extends Constraint
{
    protected $name;

    public function __construct(string $name, array $columns)
    {
        $this->name = $name;

        if (count($columns) !== count(array_unique($columns))) {
            throw new ConstraintException('Invalid unique key, duplicated column');
        }

        $this->columns = $columns;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function buildCreate(): string
    {
        return sprintf('UNIQUE KEY `%s` (`%s`)', $this->getName(), implode('`, `', $this->getColumns()));
    }

    public function buildAdd(): string
    {
        return sprintf('ADD CONSTRAINT `%s` UNIQUE (`%s`)', $this->getName(), implode('`, `', $this->getColumns()));
    }

    public function buildDrop(): string
    {
        return sprintf('DROP INDEX `%s`', $this->getName());
    }
}
