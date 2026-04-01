<?php
class SupplierQuotationsController extends Controller
{
    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('purchaseplans/index');
            return;
        }

        $quotationModel = $this->model('SupplierQuotation');
        $ok = $quotationModel->create([
            'grocery_id' => (int)($_POST['grocery_id'] ?? 0),
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
            'quoted_price' => (float)($_POST['quoted_price'] ?? 0),
            'lead_time_days' => (int)($_POST['lead_time_days'] ?? 0),
            'min_order_qty' => (float)($_POST['min_order_qty'] ?? 0),
            'quote_date' => trim($_POST['quote_date'] ?? date('Y-m-d')),
            'notes' => trim($_POST['notes'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Supplier quotation saved.' : 'Failed to save quotation.';
        $this->redirect('purchaseplans/index');
    }
}
