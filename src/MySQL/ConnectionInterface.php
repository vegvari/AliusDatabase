<?php

namespace Alius\Database\MySQL;

interface ConnectionInterface
{
    public function __construct(string $dsn, string $user, string $password, array $options = null);
    public function getOptions(): array;
    public function getPDO(): \PDO;
}
