<?php

namespace Alius\Database;

trait DefaultValue
{
    protected $default;

    public function default($value): Column
    {
        $this->default = $this->check($value);
        return $this;
    }

    public function hasDefault(): bool
    {
        return $this->default !== null;
    }

    public function getDefault()
    {
        return $this->default;
    }
}
