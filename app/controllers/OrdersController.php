<?php
class OrdersController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $orderModel = $this->model('Order');
        $tableModel = $this->model('DiningTable');

        $counts = $orderModel->getCounts();
        $orders = $orderModel->getAllWithItems();
        $kitchenQueue = $orderModel->getKitchenQueue();

        $tables = [];
        if (method_exists($tableModel, 'getAll')) {
            $tables = $tableModel->getAll();
        }

        $this->view('orders/index', [
            'title' => 'Orders',
            'counts' => is_array($counts) ? $counts : [
                'total_orders' => 0,
                'pending_orders' => 0,
                'preparing_orders' => 0,
                'ready_orders' => 0,
            ],
            'orders' => is_array($orders) ? $orders : [],
            'kitchen_queue' => is_array($kitchenQueue) ? $kitchenQueue : [],
            'tables' => is_array($tables) ? $tables : [],
        ]);
    }

    public function receipt($id): void
    {
        $this->requireLogin();

        $orderModel = $this->model('Order');
        $order = $orderModel->findByIdWithItems((int)$id);

        if (!$order) {
            $_SESSION['error'] = 'Order not found.';
            $this->redirect('orders/index');
            return;
        }

        $this->view('orders/receipt', [
            'title' => 'Order Receipt',
            'order' => $order,
        ]);
    }

    public function updateStatus($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid status update request.';
            $this->redirect('orders/index');
            return;
        }

        $status = trim($_POST['status'] ?? '');
        $orderModel = $this->model('Order');
        $ok = $orderModel->updateStatus((int)$id, $status);

        $_SESSION['success'] = $ok ? 'Order status updated successfully.' : 'Failed to update order status.';
        $this->redirect('orders/index');
    }

    public function checkout($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid checkout request.';
            $this->redirect('orders/index');
            return;
        }

        $orderModel = $this->model('Order');
        $saleModel = $this->model('Sale');
        $productModel = $this->model('Product');
        $viandModel = $this->model('Viand');
        $groceryModel = $this->model('Grocery');
        $tableModel = $this->model('DiningTable');
        $customerModel = $this->model('Customer');
        $loyaltyModel = $this->model('LoyaltyTransaction');
        $promoModel = $this->model('PromoCode');
        $promoRedemptionModel = $this->model('PromoRedemption');

        $order = $orderModel->findByIdWithItems((int)$id);
        if (!$order) {
            $_SESSION['error'] = 'Order not found.';
            $this->redirect('orders/index');
            return;
        }

        $paymentMethod = trim($_POST['payment_method'] ?? 'cash');
        $paymentReference = trim($_POST['payment_reference'] ?? '');
        $paidAmount = (float)($_POST['paid_amount'] ?? 0);
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $redeemPoints = (float)($_POST['redeem_points'] ?? 0);
        $promoCode = strtoupper(trim($_POST['promo_code'] ?? ''));

        $items = [];
        $orderTotal = 0;

        foreach (($order['items'] ?? []) as $item) {
            $qty = (float)($item['quantity'] ?? 0);
            $unitPrice = (float)($item['unit_price'] ?? 0);
            $lineTotal = $qty * $unitPrice;
            $orderTotal += $lineTotal;

            $items[] = [
                'product_id' => !empty($item['product_id']) ? (int)$item['product_id'] : null,
                'viand_id' => !empty($item['viand_id']) ? (int)$item['viand_id'] : null,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        $promoId = null;
        $promoDiscount = 0;

        if ($promoCode !== '') {
            $promo = $promoModel->findByCode($promoCode);
            if ($promo) {
                $promoDiscount = $promoModel->computeDiscount($promo, $orderTotal);
                if ($promoDiscount > 0) {
                    $promoId = (int)($promo['id'] ?? 0);
                }
            }
        }

        $discountValue = $redeemPoints + $promoDiscount;
        $netTotal = max(0, $orderTotal - $discountValue);

        if ($paidAmount < $netTotal) {
            $_SESSION['error'] = 'Paid amount is less than the bill total.';
            $this->redirect('orders/index');
            return;
        }

        foreach (($order['items'] ?? []) as $item) {
            $qty = (float)($item['quantity'] ?? 0);

            if (!empty($item['product_id'])) {
                $productModel->adjustStock((int)$item['product_id'], -$qty);
            }

            if (!empty($item['viand_id'])) {
                $ingredients = $viandModel->getIngredientsByViand((int)$item['viand_id']);
                foreach ($ingredients as $ingredient) {
                    $groceryId = (int)($ingredient['grocery_id'] ?? 0);
                    $quantityNeededPerServing = (float)($ingredient['quantity_needed'] ?? 0);

                    if ($groceryId > 0 && $quantityNeededPerServing > 0) {
                        $groceryModel->adjustStock($groceryId, -($quantityNeededPerServing * $qty));
                    }
                }
            }
        }

        $pointsEarned = $customerId > 0 ? floor($netTotal / 100) : 0;

        $saleId = $saleModel->createSaleReturningId([
            'created_by' => $_SESSION['user']['id'] ?? null,
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
            'paid_amount' => $paidAmount,
            'customer_id' => $customerId > 0 ? $customerId : null,
            'loyalty_points_earned' => $pointsEarned,
            'loyalty_points_redeemed' => $redeemPoints,
            'promo_id' => $promoId,
            'promo_discount_amount' => $promoDiscount,
        ], $items);

        if (!$saleId) {
            $_SESSION['error'] = 'Failed to complete checkout.';
            $this->redirect('orders/index');
            return;
        }

        $orderModel->attachSale((int)$order['id'], $saleId);

        if ($customerId > 0) {
            $customerModel->applySale($customerId, $netTotal, $pointsEarned);

            if ($pointsEarned > 0) {
                $loyaltyModel->create([
                    'customer_id' => $customerId,
                    'sale_id' => $saleId,
                    'transaction_type' => 'earn',
                    'points' => $pointsEarned,
                    'peso_value' => 0,
                    'notes' => 'Points earned from sale',
                    'created_by' => $_SESSION['user']['id'] ?? null,
                ]);
            }

            if ($redeemPoints > 0) {
                $customerModel->redeemPoints($customerId, $redeemPoints);
                $loyaltyModel->create([
                    'customer_id' => $customerId,
                    'sale_id' => $saleId,
                    'transaction_type' => 'redeem',
                    'points' => $redeemPoints,
                    'peso_value' => $redeemPoints,
                    'notes' => 'Points redeemed at checkout',
                    'created_by' => $_SESSION['user']['id'] ?? null,
                ]);
            }
        }

        if ($promoId && $promoDiscount > 0) {
            $promoModel->incrementUsage($promoId);
            $promoRedemptionModel->create([
                'promo_id' => $promoId,
                'sale_id' => $saleId,
                'customer_id' => $customerId > 0 ? $customerId : null,
                'discount_amount' => $promoDiscount,
            ]);
        }

        if (!empty($order['table_id'])) {
            $tableModel->setStatus((int)$order['table_id'], 'available');
        }

        $_SESSION['success'] = 'Order checked out successfully.';
        $this->redirect('orders/receipt/' . (int)$order['id']);
    }
}