<?php

namespace Alius\Database\MySQL;

class Connection implements ConnectionInterface
{
    const DEFAULT_OPTIONS = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ORACLE_NULLS       => \PDO::NULL_EMPTY_STRING,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET GLOBAL time_zone="UTC", time_zone="UTC", NAMES utf8',
    ];

    private $dsn;
    private $user;
    private $password;
    private $options;
    private $pdo;

    final public function __construct(string $dsn, string $user, string $password, array $options = null)
    {
        $this->dsn = sprintf('mysql:%s', $dsn);
        $this->user = $user;
        $this->password = $password;
        $this->options = $options === null ? static::DEFAULT_OPTIONS : $options;
    }

    final public function getOptions(): array
    {
        return $this->options;
    }

    final public function getPDO(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new \PDO($this->dsn, $this->user, $this->password, $this->getOptions());
        }

        return $this->pdo;
    }
}
