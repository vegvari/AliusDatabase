<?php

namespace Alius\Database\Interfaces;

interface TableInterface
{
    public function __construct(DatabaseInterface $database);
    public function setImmutable(): TableInterface;
    public function isImmutable(): bool;
    public function getDatabaseName(): string;
    public function setEngine(string $engine): TableInterface;
    public function getEngine(): string;
    public function setCharset(string $charset): TableInterface;
    public function getCharset(): string;
    public function setCollation(string $collation): TableInterface;
    public function getCollation(): string;
    public function setColumn(ColumnInterface $column): TableInterface;
    public function getColumn(string $column_name): ColumnInterface;
    public function getColumns(): array;
    public function hasColumn(string $column_name = null): bool;
    public function setPrimaryKeyObject(PrimaryKeyInterface $primary_key): TableInterface;
    public function setPrimaryKey(string ...$columns): TableInterface;
    public function getPrimaryKey(): PrimaryKeyInterface;
    public function hasPrimaryKey(): bool;
    public function hasCompositePrimaryKey(): bool;
    public function setUniqueKeyObject(UniqueKeyInterface $unique_key): TableInterface;
    public function setUniqueKey(string ...$columns): TableInterface;
    public function getUniqueKey(string $name): UniqueKeyInterface;
    public function getUniqueKeys(): array;
    public function hasUniqueKey(string $name = null): bool;
    public function setIndexObject(IndexInterface $index): TableInterface;
    public function setIndex(string ...$columns): TableInterface;
    public function getIndex(string $name): IndexInterface;
    public function getIndexes(): array;
    public function hasIndex(string $name = null): bool;
    public function hasIndexWithColumns(string ...$columns): bool;
    public function setForeignKeyObject(ForeignKeyInterface $foreign_key): TableInterface;
    public function setForeignKey($columns, string $parent_table, $parent_columns = null, string $on_update = 'RESTRICT', string $on_delete = 'RESTRICT'): TableInterface;
    public function getForeignKey(string $name): ForeignKeyInterface;
    public function getForeignKeys(): array;
    public function hasForeignKey(string $name = null): bool;
}
