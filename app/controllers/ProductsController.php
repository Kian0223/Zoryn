<?php
class ProductsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $productModel = $this->model('Product');
        $categoryModel = $this->model('Category');

        $this->view('products/index', [
            'title' => 'Products',
            'products' => $productModel->getAll(),
            'categories' => $categoryModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productModel = $this->model('Product');
            $ok = $productModel->create([
                'category_id' => trim($_POST['category_id'] ?? ''),
                'product_name' => trim($_POST['product_name'] ?? ''),
                'sku' => trim($_POST['sku'] ?? ''),
                'unit' => trim($_POST['unit'] ?? 'pcs'),
                'selling_price' => (float)($_POST['selling_price'] ?? 0),
                'cost_price' => (float)($_POST['cost_price'] ?? 0),
                'current_stock' => (float)($_POST['current_stock'] ?? 0),
                'low_stock_threshold' => (float)($_POST['low_stock_threshold'] ?? 5),
                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
            ]);
            $_SESSION[$ok ? 'success' : 'error'] = $ok
                ? 'Product added successfully.'
                : 'Failed to add product.';
        }

        $this->redirect('products/index');
    }

    public function update($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productModel = $this->model('Product');
            $ok = $productModel->update((int)$id, [
                'category_id' => trim($_POST['category_id'] ?? ''),
                'product_name' => trim($_POST['product_name'] ?? ''),
                'sku' => trim($_POST['sku'] ?? ''),
                'unit' => trim($_POST['unit'] ?? 'pcs'),
                'selling_price' => (float)($_POST['selling_price'] ?? 0),
                'cost_price' => (float)($_POST['cost_price'] ?? 0),
                'current_stock' => (float)($_POST['current_stock'] ?? 0),
                'low_stock_threshold' => (float)($_POST['low_stock_threshold'] ?? 5),
                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
            ]);
            $_SESSION[$ok ? 'success' : 'error'] = $ok
                ? 'Product updated successfully.'
                : 'Failed to update product.';
        }

        $this->redirect('products/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();
        $productModel = $this->model('Product');
        $ok = $productModel->delete((int)$id);

        $_SESSION[$ok ? 'success' : 'error'] = $ok
            ? 'Product deleted successfully.'
            : 'Failed to delete product.';

        $this->redirect('products/index');
    }
}
