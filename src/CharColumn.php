<?php

namespace Alius\Database;

class CharColumn extends TextColumn
{
    const TYPES = [
        'char' => 255,
        'varchar' => 65535,
    ];

    protected $length;

    public function __construct(string $name, string $type, int $length)
    {
        parent::__construct($name, $type);
        $this->length($length);
    }

    protected function length(int $length): Column
    {
        if ($length < 0
            || ($this->getType() === 'char' && $length > static::TYPES['char'])
            || ($this->getType() === 'varchar' && $length > static::TYPES['varchar'])
        ) {
            throw new ColumnException(sprintf('Invalid length for %s column: "%s"', $this->getType(), $length));
        }

        $this->length = $length;
        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function build(): string
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
