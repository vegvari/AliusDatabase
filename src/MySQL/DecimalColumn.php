<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;

class DecimalColumn extends Column
{
    protected $precision;
    protected $scale;
    protected $unsigned = false;

    public function __construct(string $name, int $precision, int $scale)
    {
        $this->name = $name;
        $this->type = 'decimal';

        if ($precision < 1 || $precision > 65) {
            throw SchemaException::invalidColumnFloatPrecision($precision);
        }

        if ($scale < 0 || $scale > 30) {
            throw SchemaException::invalidColumnFloatScale($scale);
        }

        if ($scale > $precision) {
            throw SchemaException::invalidColumnFloatScaleMax($scale, $precision);
        }

        $this->precision = $precision;
        $this->scale = $scale;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getScale(): int
    {
        return $this->scale;
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

    public function getMin(): float
    {
        if ($this->isUnsigned()) {
            return 0.0;
        }

        return (float) ('-1.0e' . ($this->getPrecision() - $this->getScale()));
    }

    public function getMax(): float
    {
        return (float) ('1.0e' . ($this->getPrecision() - $this->getScale()));
    }

    public function check($value)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (($value = filter_var($value, FILTER_VALIDATE_FLOAT)) === false) {
            throw SchemaException::invalidColumnFloatValue();
        }

        if ($value <= $this->getMin()) {
            throw SchemaException::invalidColumnFloatValueMin($this->getMin());
        }

        if ($value >= $this->getMax()) {
            throw SchemaException::invalidColumnFloatValueMax($this->getMax());
        }

        return $value;
    }

    public function buildCreate(): string
    {
        $build[] = sprintf('%s(%d,%d)', $this->buildNameAndType(), $this->getPrecision(), $this->getScale());

        if ($this->isUnsigned()) {
            $build[] = 'UNSIGNED';
        }

        if (! $this->isNullable()) {
            $build[] = 'NOT NULL';
        }

        if ($this->hasDefault()) {
            $build[] = sprintf('DEFAULT "%.' . $this->getScale() . 'F"', $this->getDefault());
        }

        if ($this->hasComment()) {
            $build[] = $this->buildComment();
        }

        return implode(' ', $build);
    }
}
