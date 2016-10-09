<?php

namespace Alius\Database;

class Index
{
    protected $name;
    protected $columns;

    public function __construct(string $name, array $columns)
    {
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

    public function getColumns(): array
    {
        return $this->columns;
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
