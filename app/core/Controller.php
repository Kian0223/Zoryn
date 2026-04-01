<?php
class Controller
{
    protected function model(string $model)
    {
        $file = APP_PATH . '/models/' . $model . '.php';
        if (file_exists($file)) {
            require_once $file;
            return new $model();
        }

        throw new Exception("Model {$model} not found.");
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $file = APP_PATH . '/views/' . $view . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }

        require_once APP_PATH . '/views/errors/404.php';
    }

    protected function redirect(string $path): void
    {
        $config = require CONFIG_PATH . '/config.php';
        header('Location: ' . rtrim($config['base_url'], '/') . '/' . ltrim($path, '/'));
        exit;
    }

    protected function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            $this->redirect('auth/index');
        }
    }
}
