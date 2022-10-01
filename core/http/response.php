<?php

namespace core\http;
use core\View;

class Response
{
    protected $headers = [];

    public ?string $layout = null;

    public ?string $title = null;

    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;

        return $this;
    }

    public function send($data)
    {
        foreach ($this->headers as $header => $value) {
            header(strtoupper($header) . ': ' . $value);
        }

        if(is_string($data)) {
            echo $data;
            return;
        }

        //return $this->body;
    }

    public function json($json)
    {
        foreach ($this->headers as $header => $value) {
            header(strtoupper($header) . ': ' . $value);
        }

        echo json_encode($json);
    }

    public function status($code)
    {
        http_response_code($code);

        return $this;
    }

    public function layout(string $layout)
    {
        $this->layout = $layout;

        return $this;
    }

    public function title(string $title) 
    {
        return $this;
    }

    public function render(string $view, $params = [])
    {
        $v = new View();

        $v->layout = $this->layout;

        echo $v->render($view, $params);

        return;
    }

    public function redirect(string $url)
    {
        header('Location: '.$url);
        exit;
    }
}