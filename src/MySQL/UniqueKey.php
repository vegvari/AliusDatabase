<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

class UniqueKey extends Constraint implements Interfaces\UniqueKeyInterface
{
    public function __construct(string $name, string ...$columns)
    {
        if ($name === '' || strtolower($name) === 'primary') {
            throw Exceptions\SchemaException::uniqueKeyInvalidName($name);
        }

        if ($columns === []) {
            throw Exceptions\SchemaException::uniqueKeyNoColumn($name);
        }

        $duplicated = array_unique(array_diff_key($columns, array_unique($columns)));
        if ($duplicated !== []) {
            throw Exceptions\SchemaException::uniqueKeyDuplicatedColumn($name, ...$duplicated);
        }

        $this->name = $name;
        $this->columns = $columns;
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
