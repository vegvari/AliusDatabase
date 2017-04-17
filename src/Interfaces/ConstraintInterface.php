<?php

namespace Alius\Database\Interfaces;

interface ConstraintInterface
{
    public function getName(): string;
    public function getColumns(): array;
    public function buildCreate(): string;
    public function buildDrop(): string;
    public function buildAdd(): string;
}
