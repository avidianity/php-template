<?php

namespace Traits;

/**
 * Allows a class to have only one instance
 */
trait Singleton
{
    /**
     * The current instance
     * 
     * @var static
     */
    protected static $instance = null;

    /**
     * Get the instance of this class
     * 
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
