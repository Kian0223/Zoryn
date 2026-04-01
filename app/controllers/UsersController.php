<?php
class UsersController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userModel = $this->model('User');
        $this->view('users/index', [
            'title' => 'Users',
            'users' => $userModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = $this->model('User');
            try {
                $userModel->create([
                    'full_name' => trim($_POST['full_name'] ?? ''),
                    'username' => trim($_POST['username'] ?? ''),
                    'password' => trim($_POST['password'] ?? ''),
                    'role' => trim($_POST['role'] ?? 'staff'),
                ]);
                $_SESSION['success'] = 'User added successfully.';
            } catch (Throwable $e) {
                $_SESSION['error'] = 'Unable to add user. Username may already exist.';
            }
        }
        $this->redirect('users/index');
    }

    public function update($id): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = $this->model('User');
            try {
                $userModel->update((int)$id, [
                    'full_name' => trim($_POST['full_name'] ?? ''),
                    'username' => trim($_POST['username'] ?? ''),
                    'password' => trim($_POST['password'] ?? ''),
                    'role' => trim($_POST['role'] ?? 'staff'),
                ]);
                $_SESSION['success'] = 'User updated successfully.';
            } catch (Throwable $e) {
                $_SESSION['error'] = 'Unable to update user.';
            }
        }
        $this->redirect('users/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();
        if ((int)$id === (int)($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['error'] = 'You cannot delete your own logged-in account.';
            $this->redirect('users/index');
        }
        $userModel = $this->model('User');
        $userModel->delete((int)$id);
        $_SESSION['success'] = 'User deleted successfully.';
        $this->redirect('users/index');
    }
}
