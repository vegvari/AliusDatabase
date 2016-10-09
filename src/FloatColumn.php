<?php

namespace Alius\Database;

class FloatColumn extends DecimalColumn
{
    const TYPES = ['float'];

    public function __construct(string $name, int $precision, int $scale, string $type = 'float')
    {
        parent::__construct($name, $precision, $scale, $type);
    }
}
