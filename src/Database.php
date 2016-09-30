<?php

namespace Alius\Database;

class Database
{
    protected $writer;
    protected $reader;

    protected $name;

    protected $engine = 'InnoDB';
    protected $charset = 'utf8';
    protected $collation = 'utf8_general_ci';
    protected $timezone = 'UTC';

    protected $tables = [];
    protected $records = [];

    public function __construct(Connection $writer, Connection ...$readers)
    {
        $this->writer = $writer->getPDO();
        $this->name = $writer->getDatabase();
        $this->charset = $writer->getCharset();

        if ($readers !== []) {
            $this->reader = $readers[array_rand($readers)]->getPDO();
        }

        $this->setUp();
    }

    protected function setUp()
    {
    }

    public function getWriter(): \PDO
    {
        return $this->writer;
    }

    public function hasReader(): bool
    {
        return $this->reader !== null;
    }

    public function getReader(): \PDO
    {
        if (! $this->hasReader() || $this->inTransaction()) {
            return $this->getWriter();
        }

        return $this->reader;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEngine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    public function getEngine(): string
    {
        return $this->engine;
    }

    public function setTimeZone($timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getTimeZone(): \DateTimeZone
    {
        if (is_string($this->timezone)) {
            $this->timezone = new \DateTimeZone($this->timezone);
        }

        return $this->timezone;
    }

    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function setCollation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }

    public function getCollation(): string
    {
        return $this->collation;
    }

    public function execute(string $query, array $data = []): \PDOStatement
    {
        $pdo = $this->getWriter();
        if (preg_match('/^select /i', $query) === 1) {
            $pdo = $this->getReader();
        }

        $statement = $pdo->prepare($query);
        $statement->execute($data);
        return $statement;
    }

    public function getLastInsertId(): int
    {
        return (int) $this->getWriter()->lastInsertId();
    }

    public function startTransaction(): bool
    {
        return $this->getWriter()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getWriter()->commit();
    }

    public function rollback(): bool
    {
        return $this->getWriter()->rollback();
    }

    public function inTransaction(): bool
    {
        return $this->getWriter()->inTransaction();
    }
}
