<?php

namespace Libraries;

use Interfaces\Arrayable;
use Interfaces\JSONable;
use stdClass;
use Traits\Singleton;

class Input implements JSONable, Arrayable
{
    use Singleton;

    protected $data = [];

    public function __construct()
    {
        foreach ($_GET as $key => $value) {
            $this->data[$key] = $value;
        }
        foreach ($_POST as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Get only the specified keys
     * 
     * @param array $keys
     * @return array
     */
    public function only($keys)
    {
        return only($this->data, $keys);
    }

    /**
     * Get except the specified keys
     * 
     * @param array $keys
     * @return array
     */
    public function except($keys)
    {
        return except($this->data, $keys);
    }

    public function all()
    {
        return $this->data;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toJSON(): object
    {
        $data = $this->toArray();
        $object = new stdClass();

        foreach ($data as $key => $value) {
            $object->{$key} = $value;
        }

        return $object;
    }
}
