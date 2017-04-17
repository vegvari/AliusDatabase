<?php

namespace Alius\Database;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

abstract class Container implements Interfaces\ContainerInterface
{
    private static $servers = [];

    public static function clearServers()
    {
        static::$servers = [];
    }

    public static function setServer(Interfaces\ServerInterface $server)
    {
        if (static::hasServer($server->getName()) && static::getServer($server->getName()) !== $server) {
            throw Exceptions\SchemaException::serverAlreadySet(static::class);
        }

        static::$servers[$server->getName()] = $server;
    }

    public static function getServer(string $name): Interfaces\ServerInterface
    {
        if (! static::hasServer($name)) {
            throw Exceptions\ContainerException::serverNotSet(static::class);
        }

        return static::$servers[$name];
    }

    public static function hasServer(string $name = null): bool
    {
        if ($name === null) {
            return static::$servers !== [];
        }

        return isset(static::$servers[$name]);
    }
}
