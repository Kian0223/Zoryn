<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="zoryn-admin-shell">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="zoryn-main-content">
        <section class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <div class="zoryn-hero-kicker">Stock Overview</div>
                <h1 class="zoryn-page-title mb-2">Inventory</h1>
                <p class="zoryn-page-subtitle mb-0">
                    <?= count($items); ?> items · <span class="text-danger fw-semibold"><?= (int)$low_count; ?> low stock</span>
                </p>
            </div>
        </section>

        <?php if ($low_count > 0): ?>
            <div class="zoryn-low-stock-alert mb-4">
                <i class="bi bi-exclamation-triangle"></i>
                <span><?= (int)$low_count; ?> item(s) are running low on stock.</span>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <table class="table zoryn-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Min Stock</th>
                            <th>Cost / Unit</th>
                            <th>Supplier</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php
                                $statusLabel = $item['stock_status'] === 'out'
                                    ? 'Out of Stock'
                                    : ($item['stock_status'] === 'low' ? 'Low Stock' : 'In Stock');
                                $statusClass = $item['stock_status'] === 'out'
                                    ? 'danger'
                                    : ($item['stock_status'] === 'low' ? 'warning' : 'success');
                            ?>
                            <tr class="<?= in_array($item['stock_status'], ['low', 'out'], true) ? 'zoryn-row-warning' : ''; ?>">
                                <td><?= htmlspecialchars($item['item_name']); ?></td>
                                <td><?= ucfirst(htmlspecialchars($item['item_type'])); ?></td>
                                <td><?= htmlspecialchars($item['category_name']); ?></td>
                                <td class="<?= in_array($item['stock_status'], ['low', 'out'], true) ? 'text-danger fw-semibold' : ''; ?>">
                                    <?= number_format((float)$item['current_stock'], 2); ?> <?= htmlspecialchars($item['unit']); ?>
                                </td>
                                <td><?= number_format((float)$item['low_stock_threshold'], 2); ?> <?= htmlspecialchars($item['unit']); ?></td>
                                <td>₱<?= number_format((float)$item['cost_price'], 2); ?></td>
                                <td><?= htmlspecialchars($item['supplier_name'] ?: '-'); ?></td>
                                <td><span class="badge text-bg-<?= $statusClass; ?>"><?= $statusLabel; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No inventory items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
