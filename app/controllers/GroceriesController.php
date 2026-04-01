<?php
class GroceriesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $groceryModel = $this->model('Grocery');

        $this->view('groceries/index', [
            'title' => 'Groceries',
            'groceries' => $groceryModel->getAll(),
            'low_stock_count' => method_exists($groceryModel, 'getLowStockCount') ? $groceryModel->getLowStockCount() : 0,
            'out_of_stock_count' => method_exists($groceryModel, 'getOutOfStockCount') ? $groceryModel->getOutOfStockCount() : 0,
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groceryModel = $this->model('Grocery');

            $grocery_name = trim($_POST['grocery_name'] ?? '');
            $unit = trim($_POST['unit'] ?? 'pcs');
            $package_count = (float)($_POST['package_count'] ?? 0);
            $package_quantity = (float)($_POST['package_quantity'] ?? 0);
            $package_cost = (float)($_POST['package_cost'] ?? 0);
            $low_stock_threshold = (float)($_POST['low_stock_threshold'] ?? 10);

            if ($grocery_name === '') {
                $_SESSION['error'] = 'Grocery name is required.';
                $this->redirect('groceries/index');
                return;
            }

            if ($package_count <= 0) {
                $_SESSION['error'] = 'Package count must be greater than 0.';
                $this->redirect('groceries/index');
                return;
            }

            if ($package_quantity <= 0) {
                $_SESSION['error'] = 'Package quantity must be greater than 0.';
                $this->redirect('groceries/index');
                return;
            }

            $current_stock = $package_count * $package_quantity;
            $latest_cost = $package_cost / $package_quantity;

            $data = [
                'grocery_name' => $grocery_name,
                'unit' => $unit,
                'current_stock' => $current_stock,
                'package_quantity' => $package_quantity,
                'package_cost' => $package_cost,
                'latest_cost' => $latest_cost,
            ];

            if (array_key_exists('low_stock_threshold', $groceryModel->getAll()[0] ?? [])) {
                $data['low_stock_threshold'] = $low_stock_threshold;
            }

            $groceryModel->create($data);

            $_SESSION['success'] = 'Grocery item added successfully.';
        }

        $this->redirect('groceries/index');
    }

    public function update($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groceryModel = $this->model('Grocery');

            $grocery_name = trim($_POST['grocery_name'] ?? '');
            $unit = trim($_POST['unit'] ?? 'pcs');
            $current_stock = (float)($_POST['current_stock'] ?? 0);
            $package_quantity = (float)($_POST['package_quantity'] ?? 0);
            $package_cost = (float)($_POST['package_cost'] ?? 0);
            $low_stock_threshold = (float)($_POST['low_stock_threshold'] ?? 10);

            if ($grocery_name === '') {
                $_SESSION['error'] = 'Grocery name is required.';
                $this->redirect('groceries/index');
                return;
            }

            if ($package_quantity <= 0) {
                $_SESSION['error'] = 'Package quantity must be greater than 0.';
                $this->redirect('groceries/index');
                return;
            }

            $latest_cost = $package_cost / $package_quantity;

            $data = [
                'grocery_name' => $grocery_name,
                'unit' => $unit,
                'current_stock' => $current_stock,
                'package_quantity' => $package_quantity,
                'package_cost' => $package_cost,
                'latest_cost' => $latest_cost,
            ];

            $existing = $groceryModel->findById((int)$id);
            if (!empty($existing) && array_key_exists('low_stock_threshold', $existing)) {
                $data['low_stock_threshold'] = $low_stock_threshold;
            }

            $groceryModel->update((int)$id, $data);

            $_SESSION['success'] = 'Grocery item updated successfully.';
        }

        $this->redirect('groceries/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();

        $groceryModel = $this->model('Grocery');
        $groceryModel->delete((int)$id);

        $_SESSION['success'] = 'Grocery item deleted successfully.';
        $this->redirect('groceries/index');
    }
}