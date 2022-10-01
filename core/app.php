<?php

namespace core;

use core\http\Request;
use core\http\Response;
use Dotenv;
use Exception;

class App
{
    public static string $root;

    public $routes = [];

    public function __construct()
    {
        self::$root = dirname(__DIR__);

        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    }

    public $controller = null;

    public static App $app;

    protected function resolve()
    {
        $verb = Request::method();
        $path = Request::path();

        $callback = $this->routes[$verb][$path] ?? false;

        if ($callback === false) {

            $error = strtoupper($verb);

            echo "<pre>Cannot $error $path</pre>";

            return;
        }

        if (is_string($callback))
            return $callback;

        if (is_array($callback)) {

            $controller = new $callback[0];

            $callback[0] = $controller;
        }

        return call_user_func($callback, new Request, new Response);
    }

    public function get(string $path, $callback): void
    {
        $this->routes['get'][$path] = $callback;
    }

    public function put(string $path, $callback): void
    {
        $this->routes['put'][$path] = $callback;
    }

    public function post(string $path, $callback): void
    {
        $this->routes['post'][$path] = $callback;
    }

    public function run()
    {
        try {
            return $this->resolve();
        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo $e->getMessage();
        }
    }
}