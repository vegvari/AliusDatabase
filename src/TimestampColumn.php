<?php

namespace Alius\Database;

class TimestampColumn extends DateTimeColumn
{
    const TYPES = ['timestamp'];

    public function __construct(string $name, string $type = 'timestamp')
    {
        parent::__construct($name, $type);
    }
}
