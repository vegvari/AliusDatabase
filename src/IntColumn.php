<?php

namespace Alius\Database;

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
            throw new ColumnException(sprintf('Invalid type for int column: "%s"', $type));
        }

        $this->type = $type;
    }

    public function setUnsigned(): Column
    {
        $this->unsigned = true;
        return $this;
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    public function setNullable(): Column
    {
        if ($this->isAutoIncrement()) {
            throw new ColumnException('Auto increment column can\'t be nullable');
        }

        return parent::setNullable();
    }

    public function setDefault($value): Column
    {
        if ($this->isAutoIncrement()) {
            throw new ColumnException('Auto increment column can\'t have default value');
        }

        return parent::setDefault($value);
    }

    public function setAutoIncrement(): Column
    {
        if ($this->hasDefault()) {
            throw new ColumnException('Auto increment column can\'t have default value');
        }

        if ($this->isNullable()) {
            throw new ColumnException('Auto increment column can\'t be nullable');
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

        if (($value = filter_var($value, FILTER_VALIDATE_INT)) === false) {
            throw new ColumnException('Value must be integer');
        }

        if ($value < $this->getMin()) {
            throw new ColumnException(sprintf('Value must be greater than %d', $this->getMin()));
        }

        if ($value > $this->getMax()) {
            throw new ColumnException(sprintf('Value must be less than %d', $this->getMax()));
        }

        return $value;
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
