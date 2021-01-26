<?php

/**
 * @method static get($url, $handle)
 * @method static post($url, $handle)
 * @method static put($url, $handle)
 * @method static patch($url, $handle)
 * @method static delete($url, $handle)
 */
class Router
{
    protected static $routes = [];
    protected static $instance = null;

    protected function register($uri, $handle, $method)
    {
        static::$routes[] = [
            'uri' => $uri,
            'handle' => $handle,
            'method' => $method,
        ];
        return $this;
    }

    public function get($uri, $handle)
    {
        return $this->register($uri, $handle, 'get');
    }

    public function post($uri, $handle)
    {
        return $this->register($uri, $handle, 'post');
    }

    public function put($uri, $handle)
    {
        return $this->register($uri, $handle, 'put');
    }

    public function patch($uri, $handle)
    {
        return $this->register($uri, $handle, 'patch');
    }

    public function delete($uri, $handle)
    {
        return $this->register($uri, $handle, 'delete');
    }

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
        return view('errors/404');
    }
}
