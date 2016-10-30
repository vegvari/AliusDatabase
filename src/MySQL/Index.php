<?php

namespace Alius\Database\MySQL;

class Index extends Constraint
{
    protected $name;

    public function __construct(string $name, array $columns)
    {
        if (strtolower($name) === 'primary') {
            throw new ConstraintException('Invalid name for index');
        }

        $this->name = $name;

        if (count($columns) !== count(array_unique($columns))) {
            throw new ConstraintException('Invalid index, duplicated column');
        }

        $this->columns = $columns;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function buildCreate(): string
    {
        return sprintf('KEY `%s` (`%s`)', $this->getName(), implode('`, `', $this->getColumns()));
    }

    public function buildAdd(): string
    {
        return sprintf('ADD INDEX `%s` (`%s`)', $this->getName(), implode('`, `', $this->getColumns()));
    }

    public function buildDrop(): string
    {
        return sprintf('DROP INDEX `%s`', $this->getName());
    }
}
