<?php

namespace Libraries;

/**
 * Base class for handling passwords
 */
class Hash
{
    public static function make($data)
    {
        return password_hash($data, PASSWORD_BCRYPT);
    }

    public static function check($data, $hash)
    {
        return password_verify($data, $hash);
    }
}
