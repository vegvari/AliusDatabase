<?php

namespace Alius\Database;

class PrimaryKey extends Constraint
{
    public function __construct(array $columns)
    {
        if (count($columns) !== count(array_unique($columns))) {
            throw new ConstraintException('Invalid primary key, duplicated column');
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
