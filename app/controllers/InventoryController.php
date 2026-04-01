<?php
class InventoryController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $productModel = $this->model('Product');
        $groceryModel = $this->model('Grocery');

        $productItems = $productModel->getInventoryItems();
        $groceryItems = $groceryModel->getInventoryItems();

        $items = array_merge($productItems, $groceryItems);

        usort($items, function ($a, $b) {
            $priority = ['out' => 0, 'low' => 1, 'ok' => 2];
            $aKey = $priority[$a['stock_status']] ?? 2;
            $bKey = $priority[$b['stock_status']] ?? 2;

            if ($aKey !== $bKey) {
                return $aKey <=> $bKey;
            }

            return strcmp($a['item_name'], $b['item_name']);
        });

        $lowCount = count(array_filter($items, fn($item) => in_array($item['stock_status'], ['low', 'out'], true)));

        $this->view('inventory/index', [
            'title' => 'Inventory',
            'items' => $items,
            'low_count' => $lowCount,
        ]);
    }
}
