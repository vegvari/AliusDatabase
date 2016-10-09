<?php

namespace Alius\Database;

class Table
{
    protected $name = '';
    protected $columns = [];
    protected $engine;
    protected $charset;
    protected $collation;
    protected $comment = '';

    protected $primary_key = [];
    protected $unique_key = [];
    protected $foreign_key = [];
    protected $index = [];

    public function __construct(string $engine, string $charset, string $collation)
    {
        $this->engine = $engine;
        $this->charset = $charset;
        $this->collation = $collation;
        $this->setUp();
    }

    public function setUp()
    {
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
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

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function hasComment(): bool
    {
        return $this->getComment() !== '';
    }

    public function setColumn(Column $column): self
    {
        if ($this->hasColumn($column->getName())) {
            throw new TableException(sprintf('Column is already set: "%s"', $column->getName()));
        }

        $this->columns[$column->getName()] = $column;

        if ($column instanceof IntColumn && $column->isAutoIncrement()) {
            $this->setPrimaryKey($column->getName());
        }

        return $this;
    }

    public function getColumn(string $column_name): Column
    {
        if (! $this->hasColumn($column_name)) {
            throw new TableException(sprintf('Column is not defined: "%s"', $column_name));
        }

        return $this->columns[$column_name];
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function hasColumn(string $column_name): bool
    {
        return isset($this->getColumns()[$column_name]);
    }

    public function setPrimaryKey(string ...$columns): self
    {
        if ($this->hasPrimaryKey()) {
            throw new TableException(sprintf('Primary key is already defined for table "%s"', $this->getName()));
        }

        foreach ($columns as $column) {
            if (! $this->hasColumn($column)) {
                throw new TableException(sprintf('Column is not defined: "%s"', $column));
            }
        }

        if (count($columns) !== count(array_unique($columns))) {
            throw new TableException('Invalid primary key, duplicated column');
        }

        $this->primary_key = $columns;
        return $this;
    }

    public function getPrimaryKey(): array
    {
        return $this->primary_key;
    }

    public function hasPrimaryKey(): bool
    {
        return $this->getPrimaryKey() !== [];
    }

    public function hasSimplePrimaryKey(): bool
    {
        return count($this->getPrimaryKey()) === 1;
    }

    public function hasCompositePrimaryKey(): bool
    {
        return $this->hasPrimaryKey() && ! $this->hasSimplePrimaryKey();
    }

    public function setUniqueKeyWithName(string $name, string ...$columns): self
    {
        if ($this->hasUniqueKey($name)) {
            throw new TableException(sprintf('Unique key "%s" is already defined for table "%s"', $name, $this->getName()));
        }

        foreach ($columns as $column) {
            if (! $this->hasColumn($column)) {
                throw new TableException(sprintf('Column is not defined: "%s"', $column));
            }
        }

        if (count($columns) !== count(array_unique($columns))) {
            throw new TableException('Invalid unique key, duplicated column');
        }

        $this->unique_key[$name] = $columns;
        return $this;
    }

    public function setUniqueKey(string ...$columns): self
    {
        array_unshift($columns, sprintf('unique_%s_%d', $this->getName(), count($this->unique_key) + 1));
        return call_user_func_array([$this, 'setUniqueKeyWithName'], $columns);
    }

    public function hasUniqueKey(string $name = null): bool
    {
        if ($name !== null) {
            return isset($this->unique_key[$name]);
        }

        return $this->unique_key !== [];
    }

    public function getUniqueKey(): array
    {
        return $this->unique_key;
    }

    public function setIndexWithName(string $name, string ...$columns): self
    {
        if ($this->hasIndex($name)) {
            throw new TableException(sprintf('Index "%s" is already defined for table "%s"', $name, $this->getName()));
        }

        foreach ($columns as $column) {
            if (! $this->hasColumn($column)) {
                throw new TableException(sprintf('Column is not defined: "%s"', $column));
            }
        }

        if (count($columns) !== count(array_unique($columns))) {
            throw new TableException('Invalid index, duplicated column');
        }

        $this->index[$name] = $columns;
        return $this;
    }

    public function setIndex(string ...$columns): self
    {
        array_unshift($columns, sprintf('index_%s_%d', $this->getName(), count($this->index) + 1));
        return call_user_func_array([$this, 'setIndexWithName'], $columns);
    }

    public function hasIndex(string $name = null): bool
    {
        if ($name !== null) {
            return isset($this->index[$name]);
        }

        return $this->index !== [];
    }

    public function getIndex(): array
    {
        return $this->index;
    }
}
