<?php

namespace Alius\Database\Interfaces;

interface ColumnInterface
{
    public function getName(): string;
    public function getType(): string;
    public function setNullable(): ColumnInterface;
    public function isNullable(): bool;
    public function setDefault($value): ColumnInterface;
    public function getDefault();
    public function hasDefault(): bool;
    public function setComment(string $comment): ColumnInterface;
    public function getComment(): string;
    public function hasComment(): bool;
    public function check($value);
    public function buildCreate(): string;
    public function buildDrop(): string;
    public function buildAdd(ColumnInterface $after = null): string;
    public function buildChange(ColumnInterface $column): string;
}
