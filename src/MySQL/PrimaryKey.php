<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;

class PrimaryKey extends Constraint
{
    public function __construct(string ...$columns)
    {
        $duplicated = array_unique(array_diff_key($columns, array_unique($columns)));
        if ($duplicated !== []) {
            throw SchemaException::primaryKeyDuplicatedColumn(...$duplicated);
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
