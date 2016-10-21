<?php

namespace Alius\Database;

class TextColumn extends Column
{
    protected static $types = [
        'tinytext' => 255,
        'text' => 65535,
        'mediumtext' => 16777215,
        'longtext' => 4294967295,
    ];

    protected $binary = false;
    protected $charset = '';
    protected $collation = '';

    public function __construct(string $name, string $type)
    {
        $this->name = $name;

        if (! isset(self::$types[$type])) {
            throw new ColumnException(sprintf('Invalid type for text column: "%s"', $type));
        }

        $this->type = $type;
    }

    public function setBinary(): Column
    {
        $this->binary = true;
        return $this;
    }

    public function isBinary(): bool
    {
        return $this->binary;
    }

    public function setCharset(string $charset): Column
    {
        $this->charset = $charset;
        return $this;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function setCollation(string $collation): Column
    {
        $this->collation = $collation;
        return $this;
    }

    public function getCollation(): string
    {
        return $this->collation;
    }

    public function getLength(): int
    {
        return static::$types[$this->getType()];
    }

    public function check($value)
    {
        if ($value === null || $value === '') {
            return;
        }

        $value = (string) $value;

        if (mb_strlen($value) > $this->getLength()) {
            throw new ColumnException(sprintf('Value is too long for %s', $this->getType()));
        }

        return $value;
    }

    public function buildCreate(): string
    {
        $build[] = $this->buildNameAndType();

        if ($this->isBinary()) {
            $build[] = 'BINARY';
        }

        if ($this->getCharset() !== '') {
            $build[] = sprintf('CHARACTER SET %s', $this->getCharset());
        }

        if ($this->getCollation() !== '') {
            $build[] = sprintf('COLLATE %s', $this->getCollation());
        }

        if (! $this->isNullable()) {
            $build[] = 'NOT NULL';
        }

        if ($this->hasComment()) {
            $build[] = $this->buildComment();
        }

        return implode(' ', $build);
    }
}
