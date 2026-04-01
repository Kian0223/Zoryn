<?php

class App
{
    protected object $controller;
    protected string $method = 'index';
    protected array $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        $controllerName = 'AuthController';
        $controllerPath = dirname(__DIR__) . '/controllers/' . $controllerName . '.php';

        if (!empty($url[0])) {
            $candidate = ucfirst($url[0]) . 'Controller';
            $candidatePath = dirname(__DIR__) . '/controllers/' . $candidate . '.php';

            if (file_exists($candidatePath)) {
                $controllerName = $candidate;
                $controllerPath = $candidatePath;
                unset($url[0]);
            }
        }

        if (!file_exists($controllerPath)) {
            die('Controller file not found: ' . $controllerPath);
        }

        require_once $controllerPath;
        $this->controller = new $controllerName();

        if (!empty($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl(): array
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }

        return [];
    }
}