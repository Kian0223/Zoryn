<?php
class PromosController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $promoModel = $this->model('PromoCode');
        $this->view('promos/index', [
            'title' => 'Promo Codes',
            'promos' => $promoModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('promos/index');
            return;
        }

        $promoModel = $this->model('PromoCode');
        $ok = $promoModel->create([
            'code' => trim($_POST['code'] ?? ''),
            'promo_name' => trim($_POST['promo_name'] ?? ''),
            'discount_type' => trim($_POST['discount_type'] ?? 'fixed'),
            'discount_value' => (float)($_POST['discount_value'] ?? 0),
            'min_spend' => (float)($_POST['min_spend'] ?? 0),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? ''),
            'usage_limit' => trim($_POST['usage_limit'] ?? ''),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'notes' => trim($_POST['notes'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Promo saved successfully.' : 'Failed to save promo.';
        $this->redirect('promos/index');
    }

    public function update($id): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('promos/index');
            return;
        }

        $promoModel = $this->model('PromoCode');
        $ok = $promoModel->update((int)$id, [
            'code' => trim($_POST['code'] ?? ''),
            'promo_name' => trim($_POST['promo_name'] ?? ''),
            'discount_type' => trim($_POST['discount_type'] ?? 'fixed'),
            'discount_value' => (float)($_POST['discount_value'] ?? 0),
            'min_spend' => (float)($_POST['min_spend'] ?? 0),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? ''),
            'usage_limit' => trim($_POST['usage_limit'] ?? ''),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'notes' => trim($_POST['notes'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Promo updated successfully.' : 'Failed to update promo.';
        $this->redirect('promos/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();
        $promoModel = $this->model('PromoCode');
        $ok = $promoModel->delete((int)$id);
        $_SESSION['success'] = $ok ? 'Promo deleted successfully.' : 'Failed to delete promo.';
        $this->redirect('promos/index');
    }
}
