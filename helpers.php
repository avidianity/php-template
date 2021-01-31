<?php

use Interfaces\Arrayable;
use Interfaces\JSONable;

/**
 * Return a file from the views folder
 * 
 * @param string $path
 * @return mixed
 */
function view($path)
{
    $path = str_replace('.', '/', $path);
    if (!file_exists(__DIR__ . '/views/' . $path . '.php')) {
        throw new LogicException($path . ' does not exist in views.');
    }
    return require_once __DIR__ . '/views/' . $path . '.php';
}

/**
 * Create an url with the given path
 * 
 * @param string $path
 * @return string
 */
function asset($path)
{
    return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $path;
}

/**
 * Dumps the data of the given value
 * 
 * @param mixed $data
 * @param string $mode
 * @return mixed
 */
function dump($data, $mode = 'var_export')
{
    if ($data instanceof JSONable) {
        $data = $data->toJSON();
    }
    if ($data instanceof Arrayable) {
        $data = $data->toArray();
    }
    if (is_array($data)) {
        $data = serializeArray($data);
    }
    return $mode($data);
}

/**
 * Recursively import all the files inside a directory
 * 
 * @param string $path
 * @return void
 */
function importRecursive($path)
{
    if (is_array($path)) {
        foreach ($path as $p) {
            importRecursive($p);
        }
        return;
    }
    if (is_string($path)) {
        foreach (glob(__DIR__ . "/{$path}/*.php") as $path) {
            require_once $path;
        }
    }
}

/**
 * Trigger a HTTP response
 * 
 * @param mixed $data
 * @param string $contentType
 * @param int $statusCode
 * @return void
 */
function response($data, $contentType = 'application/json', $statusCode = 200)
{
    if ($data instanceof JSONable) {
        $data = $data->toJSON();
    }
    if (is_array($data)) {
        $data = serializeArray($data);
    }
    echo json_encode($data);
    setHeader('Content-Type', $contentType);
    http_response_code($statusCode);
}

/**
 * Serialize an array
 * 
 * @param array $array
 * @return array
 */
function serializeArray($array)
{
    return array_map(function ($element) {
        if ($element instanceof JSONable) {
            return $element->toJSON();
        } else if ($element instanceof Arrayable) {
            return $element->toArray();
        } else if (is_array($element)) {
            return serializeArray($element);
        } else {
            return $element;
        }
    }, $array);
}

/**
 * Filters elements in an array by its keys
 * 
 * @param array $array
 * @param string[] $keys
 * @return array
 */
function except($array, $keys)
{
    return array_filter($array, function ($key) use ($keys) {
        return !in_array($key, $keys);
    }, ARRAY_FILTER_USE_KEY);
}

/**
 * Gets elements in an array that is specified in the keys
 * 
 * @param array $array
 * @param string[] $keys
 * @return array
 */
function only($array, $keys)
{
    return array_filter($array, function ($key) use ($keys) {
        return in_array($key, $keys);
    }, ARRAY_FILTER_USE_KEY);
}

/**
 * Sets a header
 * 
 * @param string $key
 * @param string $value
 * @return void
 */
function setHeader($key, $value)
{
    header("{$key}: {$value};");
}

/**
 * Gets the header values
 * 
 * @param string[] $keys
 * @return string[]
 */
function getHeaders($keys = [])
{
    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    if (count($keys) > 0) {
        return array_filter($headers, function ($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }
    return $headers;
}

/**
 * Gets a header or null if it does not exist
 * 
 * @param string $key
 * @param string|null $default
 * @return string|null
 */
function getHeader($key, $default = null)
{
    $headers = getHeaders();
    if (in_array($key, $headers)) {
        return $headers[$key];
    }

    return $default;
}

/**
 * Fetches a value from the configuration file
 * 
 * @param string|null $path
 * @return mixed
 */
function config($path = null)
{
    $configs = $_ENV['CONFIGS'];
    $value = null;
    if ($path === null) {
        return $configs;
    }
    foreach (explode('.', $path) as $key) {
        if ($value === null) {
            $value = $configs[$key];
        } else {
            $value = $value[$key];
        }
    }
    return $value;
}
