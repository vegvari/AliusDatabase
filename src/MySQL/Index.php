<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

class Index extends Constraint implements Interfaces\IndexInterface
{
    public function __construct(string $name, string ...$columns)
    {
        if ($name === '' || strtolower($name) === 'primary') {
            throw Exceptions\SchemaException::indexInvalidName($name);
        }

        if ($columns === []) {
            throw Exceptions\SchemaException::indexNoColumn($name);
        }

        $duplicated = array_unique(array_diff_key($columns, array_unique($columns)));
        if ($duplicated !== []) {
            throw Exceptions\SchemaException::indexDuplicatedColumn($name, ...$duplicated);
        }

        $this->name = $name;
        $this->columns = $columns;
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
