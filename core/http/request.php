<?php

namespace core\http;
use stdClass;

class Request
{
    public $body = null;

    public $params = null;

    public $query = null;

    public function __construct()
    {
        $this->body();
        $this->query();
    }

    public static function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public static function path()
    {
        $path = '/';

        if (isset($_GET['url'])) {
            $url = explode('/', filter_var(rtrim($_GET['url'], '/ '), FILTER_SANITIZE_URL));

            $path = '/' . $url[0];
        }

        return $path;

    /* $path = $_SERVER['REQUEST_URI'] ?? '/';
     $positon = strpos($path, '?');
     if ($positon === false) {
     return $path;
     }
     return substr($path, 0, $positon); */
    }

    public function url()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/ '), FILTER_SANITIZE_URL));
        }
    }

    private function body(): void
    {
        $body = [];

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        $attributes = json_decode(file_get_contents("php://input"));

        if ($attributes) {
            $this->body = $attributes;
        }
        else {
            foreach ($_FILES as $key => $value) {
                $body[$key] = json_decode(json_encode($_FILES[$key]));
            }

            if (self::method() === 'get') {
                foreach ($_GET as $key => $value) {
                    $body[$key] = \filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }

            if (self::method() === 'post') {
                foreach ($_POST as $key => $value) {
                    $body[$key] = \filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }

            $this->body = json_decode(json_encode($body));
        }
    }

    private function query(): void
    {
        $parts = parse_url($_SERVER['REQUEST_URI']);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);

            $this->query = json_decode(json_encode($query));
        } else {
            $this->query = array();
        }
    }
}