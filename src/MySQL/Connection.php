<?php

namespace Alius\Database\MySQL;

use Alius\Database\Interfaces;

class Connection implements Interfaces\ConnectionInterface, \Serializable
{
    const DEFAULT_OPTIONS = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ORACLE_NULLS       => \PDO::NULL_EMPTY_STRING,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET GLOBAL time_zone="UTC", time_zone="UTC", NAMES utf8',
    ];

    protected $dsn;
    protected $user;
    protected $password;
    protected $options;
    protected $pdo;

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

    final public function serialize()
    {
        return serialize([
            'dsn' => $this->dsn,
            'user' => $this->user,
            'password' => $this->password,
            'options' => $this->options,
        ]);
    }

    final public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->dsn = $data['dsn'];
        $this->user = $data['user'];
        $this->password = $data['password'];
        $this->options = $data['options'];
    }
}
