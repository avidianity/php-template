<?php

namespace Libraries;

class Storage
{
    protected static $dir = __DIR__ . '/../storage/app';

    public static function put($path, $binary)
    {
        $fullPath = static::$dir . $path;

        return file_put_contents($fullPath, $binary) !== false;
    }

    public static function get($path)
    {
        return file_get_contents(static::$dir . $path);
    }
}
