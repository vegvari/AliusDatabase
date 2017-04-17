<?php

namespace Alius\Database\Interfaces;

interface DatabaseInterface
{
    public function __construct();
    public function setEngine(string $engine): DatabaseInterface;
    public function getEngine(): string;
    public function setCharset(string $charset): DatabaseInterface;
    public function getCharset(): string;
    public function setCollation(string $collation): DatabaseInterface;
    public function getCollation(): string;
    public function setTable(string $table_class): DatabaseInterface;
    public function getTable(string $table_name): TableInterface;
    public function hasTable(string $table_name = null): bool;
    public function getTables(): array;
}
