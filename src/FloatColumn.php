<?php

namespace Alius\Database;

class FloatColumn extends DecimalColumn
{
    public function __construct(string $name, int $precision, int $scale = 0)
    {
        parent::__construct($name, $precision, $scale);
        $this->type = 'float';
    }
}
