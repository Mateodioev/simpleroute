<?php

namespace Mateodioev\HttpRouter;

class Container
{
    public static array $instances = [];

    /**
     * Create and save new class instance if not exists
     */
    public static function singleton(string $class): mixed
    {
        if (!array_key_exists($class, self::$instances)) {
            self::$instances[$class] = new $class();
        }

        return self::$instances[$class];
    }

    /**
     * Get class instance saved
     */
    public function resolve(string $class): mixed
    {
        return self::$instances[$class] ?? null;
    }
}