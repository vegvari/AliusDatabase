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
    protected $table_names = [];
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

    public function setUp()
    {
    }

    public function getWriter(): \PDO
    {
        return $this->writer;
    }

    public function getReader(): \PDO
    {
        if (! $this->hasReader() || $this->inTransaction()) {
            return $this->getWriter();
        }

        return $this->reader;
    }

    public function hasReader(): bool
    {
        return $this->reader !== null;
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

    public function setTable(string $table_class)
    {
        if (! class_exists($table_class)) {
            throw new DatabaseException(sprintf('Table class doesn\'t exist: "%s"', $table_class));
        }

        if ($this->hasTable($table_class)) {
            throw new DatabaseException(sprintf('Table is already set: "%s"', $table_class));
        }

        if (! Util::instanceOf($table_class, Table::class)) {
            throw new DatabaseException(sprintf('Class is not instance of the Table class: "%s"', $table_class));
        }

        $this->tables[$table_class] = null;
    }

    public function getTable(string $table_class): Table
    {
        if (! $this->hasTable($table_class)) {
            throw new DatabaseException(sprintf('Table class is not set: "%s"', $table_class));
        }

        if ($this->tables[$table_class] === null) {
            $this->tables[$table_class] = new $table_class($this->getName(), $this->getEngine(), $this->getCharset(), $this->getCollation());
            $this->table_names[$this->tables[$table_class]->getName()] = $table_class;
        }

        return $this->tables[$table_class];
    }

    public function hasTable(string $table_class): bool
    {
        return array_key_exists($table_class, $this->tables);
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function getTableByName(string $table_name): Table
    {
        if (! $this->hasTableByName($table_name)) {
            throw new DatabaseException(sprintf('Table class is not set with this table name: "%s"', $table_name));
        }

        return $this->getTable($this->getTableNames()[$table_name]);
    }

    public function hasTableByName(string $table_name): bool
    {
        return isset($this->getTableNames()[$table_name]);
    }

    public function getTableNames(): array
    {
        foreach ($this->tables as $table_class => $table) {
            if ($table === null) {
                $this->getTable($table_class);
            }
        }

        return $this->table_names;
    }
}
