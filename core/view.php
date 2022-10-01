<?php

namespace core;
use core\http\Response;

class View
{
    public static ?string $title = null;
    public ?string $layout = null;

    public function render($view, $params = [])
    {
        $view = $this->view($view, $params);
        $layout = $this->layout();

        return str_replace('{{content}}', $view, $layout);
    }

    private function content($content)
    {
        $layout = $this->layout();

        return str_replace('{{content}}', $content, $layout);
    }

    private function layout()
    {
        $layout = 'main';

        if($this->layout) {
            $layout = $this->layout;
        }

        ob_start();
        require_once dirname(__DIR__) . "/views/layouts/$layout.php";
        return ob_get_clean();
    }

    private function view($view, $params)
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        ob_start();
        require_once dirname(__DIR__) . "/views/$view.php";
        return ob_get_clean();
    }
}