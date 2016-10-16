<?php

namespace Alius\Database;

class DateTimeColumn extends Column
{
    protected $default_current = false;
    protected $on_update_current = false;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->type = 'datetime';
    }

    public function default($value): Column
    {
        if ($value === 'CURRENT_TIMESTAMP') {
            $this->default_current = true;
        }

        return parent::default($value);
    }

    public function defaultCurrent(): Column
    {
        return $this->default('CURRENT_TIMESTAMP');
    }

    public function isDefaultCurrent(): bool
    {
        return $this->default_current;
    }

    public function onUpdateCurrent(): Column
    {
        $this->on_update_current = true;
        return $this;
    }

    public function isOnUpdateCurrent(): bool
    {
        return $this->on_update_current;
    }

    public function check($value)
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        if ($value === 'CURRENT_TIMESTAMP') {
            $value = 'now';
        }

        $value = new \DateTimeImmutable($value);

        return $value;
    }

    public function convertFromDatabase($value)
    {
        $datetime = new \DateTimeImmutable($value, new \DateTimeZone('UTC'));
        return $datetime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }

    public function convertToDatabase(\DateTimeInterface $value): string
    {
        return $value->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    public function buildCreate(): string
    {
        $build[] = $this->buildNameAndType();

        if (! $this->isNullable()) {
            $build[] = 'NOT NULL';
        }

        if ($this->hasDefault()) {
            $default = sprintf('DEFAULT "%s"', $this->convertToDatabase($this->getDefault()));

            if ($this->isDefaultCurrent()) {
                $default = 'DEFAULT CURRENT_TIMESTAMP';
            }

            $build[] = $default;
        }

        if ($this->isOnUpdateCurrent()) {
            $build[] = 'ON UPDATE CURRENT_TIMESTAMP';
        }

        if ($this->hasComment()) {
            $build[] = $this->buildComment();
        }

        return implode(' ', $build);
    }
}
