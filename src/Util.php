<?php

namespace Alius\Database;

abstract class Util
{
    public static function instanceOf(string $class, string $wannabe_ancestor): bool
    {
        if ($class === $wannabe_ancestor) {
            return true;
        }

        $parent_class = get_parent_class($class);

        if ($parent_class === false) {
            return false;
        }

        if ($parent_class !== $wannabe_ancestor) {
            return static::instanceOf($parent_class, $wannabe_ancestor);
        }

        return true;
    }
}
