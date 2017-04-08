<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

class PrimaryKey extends Constraint implements Interfaces\PrimaryKeyInterface
{
    public function __construct(string ...$columns)
    {
        if ($columns === []) {
            throw Exceptions\SchemaException::primaryKeyNoColumn();
        }

        $duplicated = array_unique(array_diff_key($columns, array_unique($columns)));
        if ($duplicated !== []) {
            throw Exceptions\SchemaException::primaryKeyDuplicatedColumn(...$duplicated);
        }

        $this->columns = $columns;
    }

    public function isComposite(): bool
    {
        return count($this->getColumns()) > 1;
    }

    public function buildCreate(): string
    {
        return sprintf('PRIMARY KEY (`%s`)', implode('`, `', $this->getColumns()));
    }

    public function buildAdd(): string
    {
        return sprintf('ADD PRIMARY KEY (`%s`)', implode('`, `', $this->getColumns()));
    }

    public function buildDrop(): string
    {
        return 'DROP PRIMARY KEY';
    }
}
