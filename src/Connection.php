<?php

namespace Alius\Database;

class Connection
{
    const DEFAULT_OPTIONS = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ORACLE_NULLS       => \PDO::NULL_EMPTY_STRING,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET GLOBAL time_zone="UTC", time_zone="UTC"',
    ];

    protected $dsn;
    protected $user;
    protected $password;
    protected $database;
    protected $charset;
    protected $options;
    protected $pdo;

    public function __construct(string $dsn, string $user, string $password, string $database, string $charset = 'utf8', array $options = null)
    {
        $this->dsn = sprintf('mysql:%s;dbname=%s;charset=%s', $dsn, $database, $charset);
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
        $this->options = $options === null ? static::DEFAULT_OPTIONS : $options;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getPDO(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new \PDO($this->dsn, $this->user, $this->password, $this->getOptions());
        }

        return $this->pdo;
    }
}
