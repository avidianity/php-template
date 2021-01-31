<?php

namespace Libraries;

use Traits\Singleton;
use Closure;
use Exception;
use InvalidArgumentException;

/**
 * Used to bind url routes with their proper handlers.
 */
class Router
{
    use Singleton;

    protected static $routes = [];
    protected $prefix = '';

    /**
     * Registers a route
     * 
     * @param string $uri
     * @param \Closure|string $handle
     * @param string $method
     * @return static
     */
    protected function register($uri, $handle, $method)
    {
        static::$routes[] = [
            'uri' => $this->prefix . $uri,
            'handle' => $handle,
            'method' => $method,
        ];
        return $this;
    }

    /**
     * Registers a GET route
     * 
     * @param string $uri
     * @param \Closure|string $handle
     * @return static
     */
    public function get($uri, $handle)
    {
        return $this->register($uri, $handle, 'get');
    }

    /**
     * Registers a POST route
     * 
     * @param string $uri
     * @param \Closure|string $handle
     * @return static
     */
    public function post($uri, $handle)
    {
        return $this->register($uri, $handle, 'post');
    }

    /**
     * Registers a PUT route
     * 
     * @param string $uri
     * @param \Closure|string $handle
     * @return static
     */
    public function put($uri, $handle)
    {
        return $this->register($uri, $handle, 'put');
    }

    /**
     * Registers a PATCH route
     * 
     * @param string $uri
     * @param \Closure|string $handle
     * @return static
     */
    public function patch($uri, $handle)
    {
        return $this->register($uri, $handle, 'patch');
    }

    /**
     * Registers a DELETE route
     * 
     * @param string $uri
     * @param \Closure|string $handle
     * @return static
     */
    public function delete($uri, $handle)
    {
        return $this->register($uri, $handle, 'delete');
    }

    /**
     * Groups routes with a prefix
     * 
     * @param string $prefix
     * @param \Closure $callable
     * @return static
     */
    public function group($prefix, $callable)
    {
        $this->prefix = $prefix;
        $callable($this);
        $this->prefix = '';
        return $this;
    }

    /**
     * Tests the givel url against the registered routes
     * 
     * @param string $url
     * @return mixed
     */
    public function run($url)
    {
        foreach (static::$routes as $route) {
            if ($route['uri'] === $url && strtolower($_SERVER['REQUEST_METHOD']) === $route['method']) {
                if ($route['handle'] instanceof Closure) {
                    $route['handle']();
                    return;
                    break;
                }
                if (is_array($route['handle'])) {
                    $handle = $route['handle'];
                    if (!class_exists($handle[0])) {
                        throw new InvalidArgumentException('Handle must be a class of controller if array is supplied or the controller does not exist.');
                    }
                    $class = $handle[0];
                    $controller = new $class();
                    if (!method_exists($controller, $handle[1])) {
                        throw new Exception($handle[1] . ' does not exist on ' . $handle[0]);
                    }
                    $method = $handle[1];
                    $controller->{$method}();
                    return;
                    break;
                }
            }
        }
        return view('errors.404');
    }
}
