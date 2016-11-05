<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;

class UniqueKey extends Constraint
{
    protected $name;

    public function __construct(string $name, string ...$columns)
    {
        if ($name === '' || strtolower($name) === 'primary') {
            throw SchemaException::uniqueKeyInvalidName($name);
        }

        if ($columns === []) {
            throw SchemaException::uniqueKeyNoColumn($name);
        }

        $duplicated = array_unique(array_diff_key($columns, array_unique($columns)));
        if ($duplicated !== []) {
            throw SchemaException::uniqueKeyDuplicatedColumn($name, ...$duplicated);
        }

        $this->name = $name;
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
