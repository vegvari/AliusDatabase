<?php

namespace Alius\Database;

class DecimalColumn extends Column
{
    use DefaultValue;

    const TYPES = ['decimal' => true];

    protected $precision;
    protected $scale;
    protected $unsigned = false;

    public function __construct(string $name, int $precision, int $scale)
    {
        if ($precision < 1 || $precision > 65) {
            throw new ColumnException(sprintf('Invalid precision: "%d", it must be 1-65', $precision));
        }

        if ($scale < 0 || $scale > 30) {
            throw new ColumnException(sprintf('Invalid scale: "%d", it must be 0-30', $scale));
        }

        if ($scale > $precision) {
            throw new ColumnException(sprintf('Invalid scale: "%d", it must be less than precision: "%d"', $scale, $precision));
        }

        $this->precision = $precision;
        $this->scale = $scale;

        parent::__construct($name, 'decimal');
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getScale(): int
    {
        return $this->scale;
    }

    public function unsigned(): Column
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
            throw new ColumnException('Value must be float');
        }

        if ($value <= $this->getMin()) {
            throw new ColumnException(sprintf('Value must be greater than %d', $this->getMin()));
        }

        if ($value >= $this->getMax()) {
            throw new ColumnException(sprintf('Value must be less than %d', $this->getMax()));
        }

        return $value;
    }

    public function build(): string
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
