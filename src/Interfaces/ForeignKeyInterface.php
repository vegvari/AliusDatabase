<?php

namespace Alius\Database\Interfaces;

interface ForeignKeyInterface
{
    public function getParentTable(): string;
    public function getParentColumns(): array;
    public function getUpdateRule(): string;
    public function getDeleteRule(): string;
}
