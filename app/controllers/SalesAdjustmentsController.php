<?php
class SalesAdjustmentsController extends Controller
{
    public function store($saleId): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reports/index');
            return;
        }

        $saleId = (int)$saleId;
        $adjustmentType = trim($_POST['adjustment_type'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);

        if (!in_array($adjustmentType, ['void','refund'], true)) {
            $_SESSION['error'] = 'Invalid adjustment type.';
            $this->redirect('reports/index');
            return;
        }

        $saleModel = $this->model('Sale');
        $adjustmentModel = $this->model('SaleAdjustment');
        $auditModel = $this->model('AuditLog');
        $productModel = $this->model('Product');
        $groceryModel = $this->model('Grocery');
        $viandModel = $this->model('Viand');

        $sale = $saleModel->findByIdWithItems($saleId);
        if (!$sale) {
            $_SESSION['error'] = 'Sale not found.';
            $this->redirect('reports/index');
            return;
        }

        if ($adjustmentType === 'void') {
            $amount = (float)$sale['total_amount'];
            $saleModel->markVoided($saleId, $reason);

            foreach (($sale['items'] ?? []) as $item) {
                $qty = (float)($item['quantity'] ?? 0);

                if (!empty($item['product_id'])) {
                    $productModel->adjustStock((int)$item['product_id'], $qty);
                }

                if (!empty($item['viand_id'])) {
                    $ingredients = $viandModel->getIngredientsByViand((int)$item['viand_id']);
                    foreach ($ingredients as $ingredient) {
                        $groceryId = (int)($ingredient['grocery_id'] ?? 0);
                        $quantityNeeded = (float)($ingredient['quantity_needed'] ?? 0);
                        if ($groceryId > 0 && $quantityNeeded > 0) {
                            $groceryModel->adjustStock($groceryId, $quantityNeeded * $qty);
                        }
                    }
                }
            }
        } else {
            if ($amount <= 0 || $amount > (float)$sale['total_amount']) {
                $_SESSION['error'] = 'Refund amount must be greater than 0 and not exceed sale total.';
                $this->redirect('reports/index');
                return;
            }
            $saleModel->markRefunded($saleId, $reason, $amount);
        }

        $adjustmentModel->create([
            'sale_id' => $saleId,
            'adjustment_type' => $adjustmentType,
            'reason' => $reason,
            'amount' => $amount,
            'created_by' => $_SESSION['user']['id'] ?? null,
        ]);

        $auditModel->create([
            'module_name' => 'sales',
            'action_type' => $adjustmentType,
            'reference_id' => $saleId,
            'description' => ucfirst($adjustmentType) . ' applied to sale #' . $saleId . '. Reason: ' . $reason,
            'created_by' => $_SESSION['user']['id'] ?? null,
        ]);

        $_SESSION['success'] = ucfirst($adjustmentType) . ' recorded successfully.';
        $this->redirect('reports/index');
    }
}
