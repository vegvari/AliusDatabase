<?php

namespace Alius\Database;

abstract class Constraint
{
    protected $columns = [];

    public function getColumns(): array
    {
        return $this->columns;
    }

    abstract public function buildCreate(): string;

    abstract public function buildAdd(): string;

    abstract public function buildDrop(): string;
}
