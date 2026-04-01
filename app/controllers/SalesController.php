<?php
class SalesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $productModel = $this->model('Product');
        $viandModel = $this->model('Viand');
        $saleModel = $this->model('Sale');

        $this->view('sales/index', [
            'title' => 'Sales / POS',
            'products' => $productModel->getAll(),
            'viands' => $viandModel->getAllWithCost(),
            'recent_sales' => $saleModel->getSalesWithItems(date('Y-m-d'), date('Y-m-d')),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('sales/index');
            return;
        }

        $productIds = $_POST['product_id'] ?? [];
        $viandIds = $_POST['viand_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $unitPrices = $_POST['unit_price'] ?? [];
        $types = $_POST['item_type'] ?? [];

        $items = [];
        $productModel = $this->model('Product');
        $viandModel = $this->model('Viand');
        $groceryModel = $this->model('Grocery');

        foreach ($types as $i => $type) {
            $qty = (float)($quantities[$i] ?? 0);
            $price = (float)($unitPrices[$i] ?? 0);

            if ($qty <= 0 || $price < 0) {
                continue;
            }

            $productId = !empty($productIds[$i]) ? (int)$productIds[$i] : null;
            $viandId = !empty($viandIds[$i]) ? (int)$viandIds[$i] : null;

            if ($type === 'product' && !$productId) {
                continue;
            }

            if ($type === 'viand' && !$viandId) {
                continue;
            }

            $items[] = [
                'product_id' => $type === 'product' ? $productId : null,
                'viand_id'   => $type === 'viand' ? $viandId : null,
                'quantity'   => $qty,
                'unit_price' => $price,
                'line_total' => $qty * $price,
            ];

            if ($type === 'product' && $productId) {
                $productModel->adjustStock($productId, -$qty);
            }

            if ($type === 'viand' && $viandId) {
                $ingredients = $viandModel->getIngredientsByViand($viandId);

                foreach ($ingredients as $ingredient) {
                    $groceryId = (int)($ingredient['grocery_id'] ?? 0);
                    $quantityNeededPerServing = (float)($ingredient['quantity_needed'] ?? 0);

                    if ($groceryId <= 0 || $quantityNeededPerServing <= 0) {
                        continue;
                    }

                    $totalDeduction = $quantityNeededPerServing * $qty;
                    $groceryModel->adjustStock($groceryId, -$totalDeduction);
                }
            }
        }

        if (empty($items)) {
            $_SESSION['error'] = 'Please add at least one sale item.';
            $this->redirect('sales/index');
            return;
        }

        $saleModel = $this->model('Sale');
        $saleModel->createSale([
            'created_by' => $_SESSION['user']['id'] ?? null,
        ], $items);

        $_SESSION['success'] = 'Sale saved successfully.';
        $this->redirect('sales/index');
    }
}