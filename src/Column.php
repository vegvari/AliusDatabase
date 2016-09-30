<?php

namespace Alius\Database;

abstract class Column
{
    protected $name;
    protected $type;
    protected $nullable = false;
    protected $default;
    protected $comment = '';

    public function __construct(string $name, string $type)
    {
        $this->name = $name;

        if (! isset(static::TYPES[$type])) {
            throw new ColumnException(sprintf('Invalid type: "%s"', $type));
        }

        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function nullable(): Column
    {
        $this->nullable = true;
        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

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

    public function comment(string $comment): Column
    {
        $this->comment = $comment;
        return $this;
    }

    public function hasComment(): bool
    {
        return $this->getComment() !== '';
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    abstract public function check($value);

    protected function buildNameAndType(): string
    {
        return sprintf('`%s` %s', $this->getName(), $this->getType());
    }

    protected function buildComment(): string
    {
        return sprintf('COMMENT "%s"', $this->getComment());
    }

    abstract public function build(): string;

    public function __toString(): string
    {
        return $this->build();
    }


    public static function char(string $name, int $length): Column
    {
        return new CharColumn($name, 'char', $length);
    }

    public static function varchar(string $name, int $length): Column
    {
        return new CharColumn($name, 'varchar', $length);
    }
}
