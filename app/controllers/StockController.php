<?php
class StockController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $stockModel = $this->model('Stock');
        $productModel = $this->model('Product');
        $groceryModel = $this->model('Grocery');

        $this->view('stock/index', [
            'title' => 'Stock In / Out',
            'movements' => $stockModel->getMovements(),
            'products' => $productModel->getAll(),
            'groceries' => $groceryModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('stock/index');
            return;
        }

        $itemType = $_POST['item_type'] ?? 'product';
        $movementType = $_POST['movement_type'] ?? 'stock_in';
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? ($_POST['quantity_manual'] ?? 0));
        $unitCost = (float)($_POST['unit_cost'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');

        $packageCount = (float)($_POST['package_count'] ?? 0);
        $packageQuantity = (float)($_POST['package_quantity'] ?? 0);
        $packageCost = (float)($_POST['package_cost'] ?? 0);

        if ($itemId <= 0) {
            $_SESSION['error'] = 'Please complete the stock movement form.';
            $this->redirect('stock/index');
            return;
        }

        if ($itemType === 'grocery' && $movementType === 'stock_in') {
            if ($packageCount <= 0 || $packageQuantity <= 0) {
                $_SESSION['error'] = 'Package count and package quantity must be greater than 0 for grocery stock in.';
                $this->redirect('stock/index');
                return;
            }

            $quantity = $packageCount * $packageQuantity;
            $unitCost = $packageQuantity > 0 ? ($packageCost / $packageQuantity) : 0;
        }

        if ($quantity <= 0) {
            $_SESSION['error'] = 'Quantity must be greater than 0.';
            $this->redirect('stock/index');
            return;
        }

        $adjustment = $movementType === 'stock_out' ? (-1 * $quantity) : $quantity;

        $stockModel = $this->model('Stock');

        $movementSaved = $stockModel->createMovement([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'package_count' => $itemType === 'grocery' ? $packageCount : 0,
            'package_quantity' => $itemType === 'grocery' ? $packageQuantity : 0,
            'package_cost' => $itemType === 'grocery' ? $packageCost : 0,
            'unit_cost' => $unitCost,
            'remarks' => $remarks,
            'created_by' => $_SESSION['user']['id'] ?? null,
        ]);

        if (!$movementSaved) {
            $_SESSION['error'] = 'Failed to save stock movement.';
            $this->redirect('stock/index');
            return;
        }

        if ($itemType === 'product') {
            $stockAdjusted = $stockModel->adjustProductStock($itemId, $adjustment);
        } else {
            $stockAdjusted = $stockModel->adjustGroceryStock(
                $itemId,
                $adjustment,
                $movementType,
                $packageQuantity,
                $packageCost
            );
        }

        if (!$stockAdjusted) {
            $_SESSION['error'] = 'Stock movement was saved, but stock quantity update failed.';
            $this->redirect('stock/index');
            return;
        }

        $_SESSION['success'] = ucfirst(str_replace('_', ' ', $movementType)) . ' saved successfully.';
        $this->redirect('stock/index');
    }
}