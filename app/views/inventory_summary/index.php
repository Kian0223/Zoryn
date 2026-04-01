<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Inventory Summary</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Total Grocery Items</div><h3 class="mb-0"><?= (int)($totals['total_items'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-4"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Low Stock Items</div><h3 class="mb-0 text-warning"><?= (int)($totals['low_stock_items'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-4"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Total Stock Value</div><h3 class="mb-0">₱<?= number_format((float)($totals['total_stock_value'] ?? 0), 2); ?></h3></div></div></div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Unit</th>
                        <th>Current Stock</th>
                        <th>Latest Cost / Unit</th>
                        <th>Stock Value</th>
                        <th>Threshold</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($items ?? []) as $item): ?>
                        <?php
                            $stock = (float)$item['current_stock'];
                            $threshold = (float)($item['low_stock_threshold'] ?? 0);
                            $statusClass = $stock <= 0 ? 'danger' : ($stock <= $threshold ? 'warning' : 'success');
                            $statusLabel = $stock <= 0 ? 'Out' : ($stock <= $threshold ? 'Low' : 'Normal');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['grocery_name']); ?></td>
                            <td><?= htmlspecialchars($item['unit'] ?? '-'); ?></td>
                            <td><?= number_format($stock, 2); ?></td>
                            <td>₱<?= number_format((float)$item['latest_cost'], 4); ?></td>
                            <td>₱<?= number_format((float)$item['stock_value'], 2); ?></td>
                            <td><?= number_format($threshold, 2); ?></td>
                            <td><span class="badge text-bg-<?= $statusClass; ?>"><?= $statusLabel; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No inventory items found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
