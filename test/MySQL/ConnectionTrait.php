<?php

namespace Alius\Database\MySQL;

trait ConnectionTrait
{
    public function getHost(): string
    {
        if (isset($_SERVER['TRAVIS'])) {
            return '127.0.0.1';
        }

        return 'homestead';
    }

    public function getUser(): string
    {
        if (isset($_SERVER['TRAVIS'])) {
            return 'root';
        }

        return 'homestead';
    }

    public function getPassword(): string
    {
        if (isset($_SERVER['TRAVIS'])) {
            return '';
        }

        return 'secret';
    }

    public function getDatabase(): string
    {
        return 'test';
    }
}
