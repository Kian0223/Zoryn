<?php

class ViandsController extends Controller
{
    private Viand $viandModel;
    private Grocery $groceryModel;

    public function __construct()
    {
        $this->viandModel = $this->model('Viand');
        $this->groceryModel = $this->model('Grocery');
    }

    public function index(): void
    {
        $this->requireLogin();

        $this->view('viands/index', [
            'title' => 'Viands',
            'viands' => $this->viandModel->getAll(),
            'groceries' => $this->groceryModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('viands/index');
        }

        $viandName = trim($_POST['viand_name'] ?? $_POST['name'] ?? '');
        $sellingPrice = (float)($_POST['selling_price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $groceryIds = $_POST['grocery_id'] ?? [];
        $quantitiesNeeded = $_POST['quantity_needed'] ?? [];

        if ($viandName === '') {
            $_SESSION['error'] = 'Viand name is required.';
            $this->redirect('viands/index');
        }

        $hasIngredient = false;
        foreach ($groceryIds as $i => $groceryId) {
            if ((int)$groceryId > 0 && (float)($quantitiesNeeded[$i] ?? 0) > 0) {
                $hasIngredient = true;
                break;
            }
        }

        if (!$hasIngredient) {
            $_SESSION['error'] = 'Please add at least one grocery ingredient with a quantity.';
            $this->redirect('viands/index');
        }

        $created = $this->viandModel->create([
            'viand_name' => $viandName,
            'selling_price' => $sellingPrice,
            'description' => $description,
        ]);

        if (!$created) {
            $_SESSION['error'] = 'Failed to create viand.';
            $this->redirect('viands/index');
        }

        $viandId = $this->viandModel->getLastId();

        foreach ($groceryIds as $i => $groceryId) {
            $groceryId = (int)$groceryId;
            $quantityNeeded = (float)($quantitiesNeeded[$i] ?? 0);

            if ($groceryId <= 0 || $quantityNeeded <= 0) {
                continue;
            }

            $this->viandModel->addIngredient($viandId, $groceryId, $quantityNeeded);
        }

        $_SESSION['success'] = 'Viand saved successfully.';
        $this->redirect('viands/index');
    }

    public function show($id): void
    {
        $this->requireLogin();

        $summary = $this->viandModel->getCostingSummary((int)$id);
        $ingredients = $this->viandModel->getIngredientsByViand((int)$id);

        if (!$summary) {
            $_SESSION['error'] = 'Viand not found.';
            $this->redirect('viands/index');
        }

        $this->view('viands/show', [
            'title' => 'Viand Costing',
            'summary' => $summary,
            'ingredients' => $ingredients,
        ]);
    }

    public function delete($id): void
    {
        $this->requireLogin();

        if ($this->viandModel->delete((int)$id)) {
            $_SESSION['success'] = 'Viand deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete viand.';
        }

        $this->redirect('viands/index');
    }
}
