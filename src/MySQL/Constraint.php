<?php

namespace Alius\Database\MySQL;

use Alius\Database\Interfaces;

abstract class Constraint implements Interfaces\ConstraintInterface
{
    protected $name = '';
    protected $columns = [];

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getColumns(): array
    {
        return $this->columns;
    }

    abstract public function buildCreate(): string;

    abstract public function buildAdd(): string;

    abstract public function buildDrop(): string;
}
