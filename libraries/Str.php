<?php

namespace Libraries;

use Interfaces\Stringable;

class Str implements Stringable
{
    protected $data;

    public function __construct($data = '')
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return $this->data;
    }

    public static function random($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
