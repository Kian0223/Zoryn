<?php
class LoyaltyController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $customerModel = $this->model('Customer');

        $this->view('loyalty/index', [
            'title' => 'Loyalty Analytics',
            'summary' => $customerModel->getSummary(),
            'repeat_customers' => $customerModel->getTopRepeatCustomers(20),
        ]);
    }

    public function adjust($customerId): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('loyalty/index');
            return;
        }

        $customerId = (int)$customerId;
        $points = (float)($_POST['points'] ?? 0);
        $type = trim($_POST['transaction_type'] ?? 'adjust');
        $notes = trim($_POST['notes'] ?? '');

        $customerModel = $this->model('Customer');
        $loyaltyModel = $this->model('LoyaltyTransaction');
        $customer = $customerModel->findById($customerId);

        if (!$customer) {
            $_SESSION['error'] = 'Customer not found.';
            $this->redirect('loyalty/index');
            return;
        }

        if ($points <= 0) {
            $_SESSION['error'] = 'Points must be greater than 0.';
            $this->redirect('loyalty/index');
            return;
        }

        if ($type === 'redeem') {
            $customerModel->redeemPoints($customerId, $points);
        } else {
            $customerModel->applySale($customerId, 0, $points);
            $type = 'adjust';
        }

        $loyaltyModel->create([
            'customer_id' => $customerId,
            'sale_id' => null,
            'transaction_type' => $type,
            'points' => $points,
            'peso_value' => 0,
            'notes' => $notes,
            'created_by' => $_SESSION['user']['id'] ?? null,
        ]);

        $_SESSION['success'] = 'Loyalty points updated successfully.';
        $this->redirect('loyalty/index');
    }
}
