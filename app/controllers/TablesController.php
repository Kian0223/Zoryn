<?php
class TablesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $tableModel = $this->model('DiningTable');

        $this->view('tables/index', [
            'title' => 'Tables',
            'tables' => $tableModel->getAll(),
            'summary' => $tableModel->getSummary(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tableModel = $this->model('DiningTable');
            $tableName = trim($_POST['table_name'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            $area = trim($_POST['area'] ?? '');
            $status = trim($_POST['status'] ?? 'available');

            if ($tableName === '' || $capacity <= 0) {
                $_SESSION['error'] = 'Please enter a valid table name and capacity.';
                $this->redirect('tables/index');
                return;
            }

            $tableModel->create([
                'table_name' => $tableName,
                'capacity' => $capacity,
                'area' => $area,
                'status' => $status,
            ]);

            $_SESSION['success'] = 'Table added successfully.';
        }

        $this->redirect('tables/index');
    }

    public function update($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tableModel = $this->model('DiningTable');
            $tableName = trim($_POST['table_name'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            $area = trim($_POST['area'] ?? '');
            $status = trim($_POST['status'] ?? 'available');

            if ($tableName === '' || $capacity <= 0) {
                $_SESSION['error'] = 'Please enter a valid table name and capacity.';
                $this->redirect('tables/index');
                return;
            }

            $tableModel->update((int)$id, [
                'table_name' => $tableName,
                'capacity' => $capacity,
                'area' => $area,
                'status' => $status,
            ]);

            $_SESSION['success'] = 'Table updated successfully.';
        }

        $this->redirect('tables/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();
        $tableModel = $this->model('DiningTable');
        $tableModel->delete((int)$id);
        $_SESSION['success'] = 'Table deleted successfully.';
        $this->redirect('tables/index');
    }
}
