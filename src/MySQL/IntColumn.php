<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

class IntColumn extends Column
{
    const TYPES = [
        'tinyint' => ['signed' => 127, 'unsigned' => 255],
        'smallint' => ['signed' => 32767, 'unsigned' => 65535],
        'mediumint' => ['signed' => 8388607, 'unsigned' => 16777215],
        'int' => ['signed' => 2147483647, 'unsigned' => 4294967295],
        'bigint' => ['signed' => PHP_INT_MAX, 'unsigned' => PHP_INT_MAX],
    ];

    protected static $types = [
        'tinyint' => ['signed' => 127, 'unsigned' => 255],
        'smallint' => ['signed' => 32767, 'unsigned' => 65535],
        'mediumint' => ['signed' => 8388607, 'unsigned' => 16777215],
        'int' => ['signed' => 2147483647, 'unsigned' => 4294967295],
        'bigint' => ['signed' => PHP_INT_MAX, 'unsigned' => PHP_INT_MAX],
    ];

    protected $unsigned = false;
    protected $auto_increment = false;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;

        if (! isset(self::$types[$type])) {
            throw Exceptions\SchemaException::invalidColumnType($type);
        }

        $this->type = $type;
    }

    public function setUnsigned(): Interfaces\ColumnInterface
    {
        $this->unsigned = true;
        return $this;
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    public function setNullable(): Interfaces\ColumnInterface
    {
        if ($this->isAutoIncrement()) {
            throw Exceptions\SchemaException::invalidColumnIntAutoIncrementNullable();
        }

        return parent::setNullable();
    }

    public function setDefault($value): Interfaces\ColumnInterface
    {
        if ($this->isAutoIncrement()) {
            throw Exceptions\SchemaException::invalidColumnIntAutoIncrementDefault();
        }

        return parent::setDefault($value);
    }

    public function setAutoIncrement(): Interfaces\ColumnInterface
    {
        if ($this->isNullable()) {
            throw Exceptions\SchemaException::invalidColumnIntAutoIncrementNullable();
        }

        if ($this->hasDefault()) {
            throw Exceptions\SchemaException::invalidColumnIntAutoIncrementDefault();
        }

        $this->auto_increment = true;
        return $this;
    }

    public function isAutoIncrement(): bool
    {
        return $this->auto_increment;
    }

    public function getMin(): int
    {
        if ($this->isUnsigned()) {
            return 0;
        }

        return ~static::$types[$this->getType()]['signed'];
    }

    public function getMax(): int
    {
        if (! $this->isUnsigned()) {
            return static::$types[$this->getType()]['signed'];
        }

        return static::$types[$this->getType()]['unsigned'];
    }

    public function check($value)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (bccomp($value, $this->getMin()) === -1) {
            throw Exceptions\SchemaException::invalidColumnIntValueMin($this->getMin());
        }

        if (bccomp($value, $this->getMax()) === 1) {
            throw Exceptions\SchemaException::invalidColumnIntValueMax($this->getMax());
        }

        return (int) $value;
    }

    public function buildCreate(): string
    {
        $build[] = $this->buildNameAndType();

        if ($this->isUnsigned()) {
            $build[] = 'UNSIGNED';
        }

        if (! $this->isNullable()) {
            $build[] = 'NOT NULL';
        }

        if ($this->isAutoIncrement()) {
            $build[] = 'AUTO_INCREMENT';
        }

        if ($this->hasDefault()) {
            $build[] = sprintf('DEFAULT "%d"', $this->getDefault());
        }

        if ($this->hasComment()) {
            $build[] = $this->buildComment();
        }

        return implode(' ', $build);
    }
}
