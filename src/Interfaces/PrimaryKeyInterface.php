<?php

namespace Alius\Database\Interfaces;

interface PrimaryKeyInterface
{
    public function isComposite(): bool;
}
