<?php
class AuthController extends Controller
{
    public function index(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->redirect('dashboard/index');
        }

        $this->view('auth/login', [
            'title' => 'Login',
            'error' => $_SESSION['login_error'] ?? null
        ]);
        unset($_SESSION['login_error']);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/index');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['login_error'] = 'Username and password are required.';
            $this->redirect('auth/index');
        }

        $userModel = $this->model('User');
        $user = $userModel->findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_error'] = 'Invalid username or password.';
            $this->redirect('auth/index');
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        $this->redirect('dashboard/index');
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['login_error'] = 'You have been logged out.';
        $this->redirect('auth/index');
    }
}
