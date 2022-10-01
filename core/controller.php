<?php

namespace core;

class Controller
{
    public string $layout;

    public function layout(string $layout)
    {
        $this->layout = $layout;
    }

    protected function render($view, $params = [])
    {
        return 'good';
    }
}