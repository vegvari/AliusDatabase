<?php

namespace Alius\Database;

class TimestampColumn extends DateTimeColumn
{
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->type = 'timestamp';
    }
}
