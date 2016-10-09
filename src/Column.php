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

        if (! isset(static::TYPES[$type]) && array_search($type, static::TYPES) === false) {
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

    public static function datetime(string $name): Column
    {
        return new DateTimeColumn($name);
    }

    public static function decimal(string $name, int $precision, int $scale): Column
    {
        return new DecimalColumn($name, $precision, $scale);
    }

    public static function float(string $name, int $precision, int $scale): Column
    {
        return new FloatColumn($name, $precision, $scale);
    }

    public static function tinyint(string $name): Column
    {
        return new IntColumn($name, 'tinyint');
    }

    public static function smallint(string $name): Column
    {
        return new IntColumn($name, 'smallint');
    }

    public static function mediumint(string $name): Column
    {
        return new IntColumn($name, 'mediumint');
    }

    public static function int(string $name): Column
    {
        return new IntColumn($name, 'int');
    }

    public static function bigint(string $name): Column
    {
        return new IntColumn($name, 'bigint');
    }

    public static function tinyserial(string $name): Column
    {
        return static::tinyint($name)->unsigned()->autoIncrement();
    }

    public static function smallserial(string $name): Column
    {
        return static::smallint($name)->unsigned()->autoIncrement();
    }

    public static function mediumserial(string $name): Column
    {
        return static::mediumint($name)->unsigned()->autoIncrement();
    }

    public static function serial(string $name): Column
    {
        return static::int($name)->unsigned()->autoIncrement();
    }

    public static function bigserial(string $name): Column
    {
        return static::bigint($name)->unsigned()->autoIncrement();
    }

    public static function tinytext(string $name): Column
    {
        return new TextColumn($name, 'tinytext');
    }

    public static function text(string $name): Column
    {
        return new TextColumn($name, 'text');
    }

    public static function mediumtext(string $name): Column
    {
        return new TextColumn($name, 'mediumtext');
    }

    public static function longtext(string $name): Column
    {
        return new TextColumn($name, 'longtext');
    }

    public static function timestamp(string $name): Column
    {
        return new TimestampColumn($name);
    }
}
