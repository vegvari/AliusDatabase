<?php

namespace Alius\Database\MySQL;

use Alius\Database\Interfaces;

abstract class Column implements Interfaces\ColumnInterface
{
    protected $name;
    protected $type;
    protected $nullable = false;
    protected $default;
    protected $comment = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setNullable(): Interfaces\ColumnInterface
    {
        $this->nullable = true;
        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function setDefault($value): Interfaces\ColumnInterface
    {
        $this->default = $this->check($value);
        return $this;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function hasDefault(): bool
    {
        return $this->getDefault() !== null;
    }

    public function setComment(string $comment): Interfaces\ColumnInterface
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function hasComment(): bool
    {
        return $this->getComment() !== '';
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

    abstract public function buildCreate(): string;

    public function buildDrop(): string
    {
        return sprintf('DROP COLUMN `%s`', $this->getName());
    }

    public function buildAdd(Interfaces\ColumnInterface $after = null): string
    {
        $build = sprintf('ADD COLUMN %s', $this->buildCreate());

        if ($after !== null) {
            $build .= sprintf(' AFTER `%s`', $after->getName());
        }

        return $build;
    }

    public function buildChange(Interfaces\ColumnInterface $column): string
    {
        return sprintf('CHANGE COLUMN `%s` %s', $column->getName(), $this->buildCreate());
    }

    public static function char(string $name, int $length): CharColumn
    {
        return new CharColumn($name, 'char', $length);
    }

    public static function varchar(string $name, int $length): CharColumn
    {
        return new CharColumn($name, 'varchar', $length);
    }

    public static function datetime(string $name): DateTimeColumn
    {
        return new DateTimeColumn($name);
    }

    public static function decimal(string $name, int $precision, int $scale): DecimalColumn
    {
        return new DecimalColumn($name, $precision, $scale);
    }

    public static function float(string $name, int $precision, int $scale = null): FloatColumn
    {
        return new FloatColumn($name, $precision, $scale);
    }

    public static function tinyint(string $name): IntColumn
    {
        return new IntColumn($name, 'tinyint');
    }

    public static function smallint(string $name): IntColumn
    {
        return new IntColumn($name, 'smallint');
    }

    public static function mediumint(string $name): IntColumn
    {
        return new IntColumn($name, 'mediumint');
    }

    public static function int(string $name): IntColumn
    {
        return new IntColumn($name, 'int');
    }

    public static function bigint(string $name): IntColumn
    {
        return new IntColumn($name, 'bigint');
    }

    public static function tinyserial(string $name): IntColumn
    {
        return static::tinyint($name)->setUnsigned()->setAutoIncrement();
    }

    public static function smallserial(string $name): IntColumn
    {
        return static::smallint($name)->setUnsigned()->setAutoIncrement();
    }

    public static function mediumserial(string $name): IntColumn
    {
        return static::mediumint($name)->setUnsigned()->setAutoIncrement();
    }

    public static function serial(string $name): IntColumn
    {
        return static::int($name)->setUnsigned()->setAutoIncrement();
    }

    public static function bigserial(string $name): IntColumn
    {
        return static::bigint($name)->setUnsigned()->setAutoIncrement();
    }

    public static function tinytext(string $name): TextColumn
    {
        return new TextColumn($name, 'tinytext');
    }

    public static function text(string $name): TextColumn
    {
        return new TextColumn($name, 'text');
    }

    public static function mediumtext(string $name): TextColumn
    {
        return new TextColumn($name, 'mediumtext');
    }

    public static function longtext(string $name): TextColumn
    {
        return new TextColumn($name, 'longtext');
    }

    public static function timestamp(string $name): TimestampColumn
    {
        return new TimestampColumn($name);
    }
}
