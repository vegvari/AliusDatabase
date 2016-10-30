<?php

namespace Alius\Database\MySQL;

class CharColumn extends TextColumn
{
    protected static $types = [
        'char' => 255,
        'varchar' => 65535,
    ];

    protected $length;

    public function __construct(string $name, string $type, int $length)
    {
        $this->name = $name;

        if (! isset(self::$types[$type])) {
            throw new ColumnException(sprintf('Invalid type for char column: "%s"', $type));
        }

        $this->type = $type;

        if ($length < 0
            || ($this->getType() === 'char' && $length > static::$types['char'])
            || ($this->getType() === 'varchar' && $length > static::$types['varchar'])
        ) {
            throw new ColumnException(sprintf('Invalid length for %s column: "%s"', $this->getType(), $length));
        }

        $this->length = $length;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function buildCreate(): string
    {
        $build[] = sprintf('%s(%d)', $this->buildNameAndType(), $this->getLength());

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

        if ($this->hasDefault()) {
            $build[] = sprintf('DEFAULT "%s"', $this->getDefault());
        }

        if ($this->hasComment()) {
            $build[] = $this->buildComment();
        }

        return implode(' ', $build);
    }
}
