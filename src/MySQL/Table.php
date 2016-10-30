<?php

namespace Alius\Database\MySQL;

class Table
{
    protected $database_name;
    protected $name = '';
    protected $columns = [];
    protected $engine;
    protected $charset;
    protected $collation;
    protected $comment = '';

    protected $primary_key;
    protected $unique_key = [];
    protected $foreign_key = [];
    protected $index = [];

    public function __construct(string $database_name, string $engine, string $charset, string $collation)
    {
        $this->database_name = $database_name;
        $this->engine = $engine;
        $this->charset = $charset;
        $this->collation = $collation;
        $this->setUp();
    }

    public function setUp()
    {
    }

    public function getDatabaseName(): string
    {
        return $this->database_name;
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

    public function getColumn(string $name): Column
    {
        if (! $this->hasColumn($name)) {
            throw new TableException(sprintf('Column is not defined: "%s"', $name));
        }

        return $this->columns[$name];
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function hasColumn(string $name): bool
    {
        return isset($this->getColumns()[$name]);
    }

    public function setPrimaryKeyObject(PrimaryKey $primary_key): self
    {
        if ($this->hasPrimaryKey()) {
            throw new TableException(sprintf('Primary key is already defined for table "%s"', $this->getName()));
        }

        foreach ($primary_key->getColumns() as $column) {
            if (! $this->hasColumn($column)) {
                throw new TableException(sprintf('Column is not defined: "%s"', $column));
            }
        }

        $this->primary_key = $primary_key;
        return $this;
    }

    public function setPrimaryKey(string ...$columns): self
    {
        return $this->setPrimaryKeyObject(new PrimaryKey($columns));
    }

    public function getPrimaryKey(): PrimaryKey
    {
        return $this->primary_key;
    }

    public function hasPrimaryKey(): bool
    {
        return $this->primary_key !== null;
    }

    public function hasSimplePrimaryKey(): bool
    {
        return $this->hasPrimaryKey() && ! $this->getPrimaryKey()->isComposite();
    }

    public function hasCompositePrimaryKey(): bool
    {
        return $this->hasPrimaryKey() && $this->getPrimaryKey()->isComposite();
    }

    public function setUniqueKeyObject(UniqueKey $unique_key): self
    {
        if ($this->hasUniqueKey($unique_key->getName())) {
            throw new TableException(sprintf('Unique key "%s" is already defined for table "%s"', $unique_key->getName(), $this->getName()));
        }

        foreach ($unique_key->getColumns() as $column) {
            if (! $this->hasColumn($column)) {
                throw new TableException(sprintf('Column is not defined: "%s"', $column));
            }
        }

        $this->unique_key[$unique_key->getName()] = $unique_key;
        return $this;
    }

    public function setUniqueKeyWithName(string $name, string ...$columns): self
    {
        return $this->setUniqueKeyObject(new UniqueKey($name, $columns));
    }

    public function setUniqueKey(string ...$columns): self
    {
        return $this->setUniqueKeyWithName(sprintf('unique-%s', implode('-', $columns)), ...$columns);
    }

    public function getUniqueKey(string $name): UniqueKey
    {
        if (! $this->hasUniqueKey($name)) {
            throw new TableException(sprintf('Unique key is not defined: "%s"', $name));
        }

        return $this->unique_key[$name];
    }

    public function getUniqueKeys(): array
    {
        return $this->unique_key;
    }

    public function hasUniqueKey(string $name = null): bool
    {
        if ($name !== null) {
            return isset($this->unique_key[$name]);
        }

        return $this->unique_key !== [];
    }

    public function setIndexObject(Index $index): self
    {
        if ($this->hasIndex($index->getName())) {
            throw new TableException(sprintf('Index "%s" is already defined for table "%s"', $index->getName(), $this->getName()));
        }

        foreach ($index->getColumns() as $column) {
            if (! $this->hasColumn($column)) {
                throw new TableException(sprintf('Column is not defined: "%s"', $column));
            }
        }

        $this->index[$index->getName()] = $index;
        return $this;
    }

    public function setIndexWithName(string $name, string ...$columns): self
    {
        return $this->setIndexObject(new Index($name, $columns));
    }

    public function setIndex(string ...$columns): self
    {
        return $this->setIndexWithName(sprintf('index-%s', implode('-', $columns)), ...$columns);
    }

    public function getIndex(string $name): Index
    {
        if (! $this->hasIndex($name)) {
            throw new TableException(sprintf('Index is not defined: "%s"', $name));
        }

        return $this->index[$name];
    }

    public function getIndexes(): array
    {
        return $this->index;
    }

    public function getIndexWithColumns(string ...$columns)
    {
        foreach ($this->getIndexes() as $index) {
            if ($index->getColumns() === $columns) {
                return $index;
            }
        }
    }

    public function hasIndex(string $name = null): bool
    {
        if ($name !== null) {
            return isset($this->index[$name]);
        }

        return $this->index !== [];
    }

    public function hasIndexWithColumns(string ...$columns): bool
    {
        return $this->getIndexWithColumns(...$columns) !== null;
    }

    public function setForeignKeyObject(ForeignKey $foreign_key): self
    {
        if ($this->getEngine() === 'TEMPORARY') {
            throw new TableException('Temporary tables can\'t have foreign key');
        }

        if ($this->hasForeignKey($foreign_key->getName())) {
            throw new TableException(sprintf('Foreign key is already defined: "%s"', $foreign_key->getName()));
        }

        foreach ($foreign_key->getColumns() as $column) {
            if (! $this->hasColumn($column)) {
                throw new TableException(sprintf('Column is not defined: "%s"', $column));
            }
        }

        $this->foreign_key[$foreign_key->getName()] = $foreign_key;
        $this->setIndexWithName($foreign_key->getName(), ...$foreign_key->getColumns());

        return $this;
    }

    public function setForeignKeyWithName(string $name, $columns, string $parent_table, $parent_columns = null, string $on_update = 'RESTRICT', string $on_delete = 'RESTRICT'): self
    {
        return $this->setForeignKeyObject(new ForeignKey($name, $columns, $parent_table, $parent_columns, $on_update, $on_delete));
    }

    public function setForeignKey($columns, string $parent_table, $parent_columns = null, string $on_update = 'RESTRICT', string $on_delete = 'RESTRICT'): self
    {
        $name = sprintf('%s_ibfk_%d', $this->getName(), count($this->foreign_key) + 1);
        return $this->setForeignKeyWithName($name, $columns, $parent_table, $parent_columns, $on_update, $on_delete);
    }

    public function getForeignKey(string $name): ForeignKey
    {
        if (! $this->hasForeignKey($name)) {
            throw new TableException(sprintf('Foreign key is not defined: "%s"', $name));
        }

        return $this->foreign_key[$name];
    }

    public function getForeignKeys(): array
    {
        return $this->foreign_key;
    }

    public function hasForeignKey(string $name = null): bool
    {
        if ($name !== null) {
            return isset($this->foreign_key[$name]);
        }

        return $this->foreign_key !== [];
    }

    public function buildCreate(): string
    {
        $create = [];

        if ($this->getColumns() !== []) {
            foreach ($this->getColumns() as $column) {
                $create[] = $column->buildCreate();
            }
        }

        if ($this->hasPrimaryKey()) {
            $create[] = $this->getPrimaryKey()->buildCreate();
        }

        foreach ($this->getUniqueKeys() as $unique_key) {
            $create[] = $unique_key->buildCreate();
        }

        foreach ($this->getIndexes() as $index) {
            $create[] = $index->buildCreate();
        }

        foreach ($this->getForeignKeys() as $foreign_key) {
            $create[] = $foreign_key->buildCreate();
        }

        $build[] = sprintf('CREATE TABLE `%s`', $this->getName());
        $build[] = sprintf('(%s)', implode(', ', $create));
        $build[] = sprintf('ENGINE=%s', $this->getEngine());
        $build[] = sprintf('CHARSET=%s', $this->getCharset());
        $build[] = sprintf('COLLATE=%s', $this->getCollation());

        if ($this->hasComment()) {
            $build[] = sprintf('COMMENT="%s"', $this->getComment());
        }

        return implode(' ', $build) . ';';
    }

    public function buildDrop(): string
    {
        return sprintf('DROP TABLE `%s`;', $this->getName());
    }

    public function buildAlterColumns(Table $table): array
    {
        $build = [];

        foreach ($this->getColumns() as $column) {
            if (! $table->hasColumn($column->getName())) {
                $build[] = $column->buildDrop();
            }
        }

        $after = null;
        foreach ($table->getColumns() as $column) {
            if (! $this->hasColumn($column->getName())) {
                $build[] = $column->buildAdd($after);
            } elseif ($column != $this->getColumn($column->getName())) {
                $build[] = $column->buildChange($this->getColumn($column->getName()));
            }

            $after = $column;
        }

        return $build;
    }

    public function buildAlterPrimaryKeys(Table $table): array
    {
        $build = [];

        if ($this->hasPrimaryKey()) {
            if (! $table->hasPrimaryKey()) {
                $build[] = $this->getPrimaryKey()->buildDrop();
            } elseif ($this->getPrimaryKey() != $table->getPrimaryKey()) {
                $build[] = $this->getPrimaryKey()->buildDrop();
                $build[] = $table->getPrimaryKey()->buildAdd();
            }
        } elseif ($table->hasPrimaryKey()) {
            $build[] = $table->getPrimaryKey()->buildAdd();
        }

        return $build;
    }

    public function buildAlterUniqueKeys(Table $table): array
    {
        $build = [];

        foreach ($this->getUniqueKeys() as $name => $unique_key) {
            if (! $table->hasUniqueKey($name) || $table->getUniqueKey($name) != $unique_key) {
                $build[] = $unique_key->buildDrop();
            }
        }

        foreach ($table->getUniqueKeys() as $name => $unique_key) {
            if (! $this->hasUniqueKey($name) || $this->getUniqueKey($name) != $unique_key) {
                $build[] = $unique_key->buildAdd();
            }
        }

        return $build;
    }

    public function buildAlterIndexes(Table $table): array
    {
        $build = [];

        foreach ($this->getIndexes() as $name => $index) {
            if (! $table->hasIndex($name) || $table->getIndex($name) != $index) {
                $build[] = $index->buildDrop();
            }
        }

        foreach ($table->getIndexes() as $name => $index) {
            if (! $this->hasIndex($name) || $this->getIndex($name) != $index) {
                $build[] = $index->buildAdd();
            }
        }

        return $build;
    }

    public function buildAlterDropForeignKeys(Table $table): array
    {
        $build = [];

        foreach ($this->getForeignKeys() as $name => $fk) {
            if (! $table->hasForeignKey($name) || $table->getForeignKey($name) != $fk) {
                $build[] = $fk->buildDrop();
            }
        }

        return $build;
    }

    public function buildAlterAddForeignKeys(Table $table): array
    {
        $build = [];

        foreach ($table->getForeignKeys() as $name => $fk) {
            if (! $this->hasForeignKey($name) || $this->getForeignKey($name) != $fk) {
                $build[] = $fk->buildAdd();
            }
        }

        return $build;
    }

    public function buildAlter(Table $table): array
    {
        $build = [];

        // column
        if (($columns = $this->buildAlterColumns($table)) !== []) {
            $build = array_merge($build, $columns);
        }

        // primary key
        if (($primary_keys = $this->buildAlterPrimaryKeys($table)) !== []) {
            $build = array_merge($build, $primary_keys);
        }

        // unique key
        if (($unique_keys = $this->buildAlterUniqueKeys($table)) !== []) {
            $build = array_merge($build, $unique_keys);
        }

        // indexes
        if (($indexes = $this->buildAlterIndexes($table)) !== []) {
            $build = array_merge($build, $indexes);
        }

        // foreign key
        $drop_foreign_key = $this->buildAlterDropForeignKeys($table);
        $add_foreign_key = $this->buildAlterAddForeignKeys($table);

        if ($build !== []) {
            if ($drop_foreign_key !== [] && $add_foreign_key !== []) {
                $alter[] = sprintf('ALTER TABLE `%s` %s;', $this->getName(), implode(', ', array_merge($build, $drop_foreign_key)));
                $alter[] = sprintf('ALTER TABLE `%s` %s;', $this->getName(), implode(', ', $add_foreign_key));

                return $alter;
            }

            return [sprintf('ALTER TABLE `%s` %s;', $this->getName(), implode(', ', array_merge($build, $drop_foreign_key, $add_foreign_key)))];
        }

        return $build;
    }
}
