<?php

namespace Alius\Database\Exceptions;

use Alius\Database\Interfaces;

class ContainerException extends RuntimeException
{
    public static function serverNotSet(string $server_class): Interfaces\ExceptionInterface
    {
        throw new static(sprintf('Server is not set in container: "%s"', $server_class), self::CONTAINER_SERVER_NOT_SET);
    }
}
