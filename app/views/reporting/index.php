<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Performance Reports</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="reporting-card card shadow-sm border-0 mb-4">
            <div class="card-body table-responsive">
                <h5 class="mb-3">Menu Engineering</h5>
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Viand</th>
                            <th>Selling Price</th>
                            <th>Food Cost</th>
                            <th>Profit</th>
                            <th>Qty Sold</th>
                            <th>Sales Total</th>
                            <th>Class</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($menu_engineering ?? []) as $row): ?>
                            <?php
                                $class = $row['menu_class'] ?? 'Dog';
                                $badge = 'secondary';
                                if ($class === 'Star') $badge = 'success';
                                elseif ($class === 'Plowhorse') $badge = 'warning';
                                elseif ($class === 'Puzzle') $badge = 'info';
                                elseif ($class === 'Dog') $badge = 'danger';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['viand_name']); ?></td>
                                <td>₱<?= number_format((float)$row['selling_price'], 2); ?></td>
                                <td>₱<?= number_format((float)$row['food_cost'], 2); ?></td>
                                <td>₱<?= number_format((float)$row['estimated_profit'], 2); ?></td>
                                <td><?= number_format((float)$row['qty_sold'], 2); ?></td>
                                <td>₱<?= number_format((float)$row['sales_total'], 2); ?></td>
                                <td><span class="badge text-bg-<?= $badge; ?>"><?= htmlspecialchars($class); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($menu_engineering)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No viand sales data found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="reporting-card card shadow-sm border-0">
                    <div class="card-body table-responsive">
                        <h5 class="mb-3">Best-Selling Viands</h5>
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Viand</th>
                                    <th>Qty Sold</th>
                                    <th>Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($best_viands ?? []) as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['viand_name']); ?></td>
                                        <td><?= number_format((float)$row['total_qty_sold'], 2); ?></td>
                                        <td>₱<?= number_format((float)$row['total_sales'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($best_viands)): ?>
                                    <tr><td colspan="3" class="text-center text-muted">No viand data found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="reporting-card card shadow-sm border-0">
                    <div class="card-body table-responsive">
                        <h5 class="mb-3">Best-Selling Products</h5>
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty Sold</th>
                                    <th>Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($best_products ?? []) as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['product_name']); ?></td>
                                        <td><?= number_format((float)$row['total_qty_sold'], 2); ?></td>
                                        <td>₱<?= number_format((float)$row['total_sales'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($best_products)): ?>
                                    <tr><td colspan="3" class="text-center text-muted">No product data found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="reporting-card card shadow-sm border-0">
                    <div class="card-body table-responsive">
                        <h5 class="mb-3">Slow-Moving Groceries</h5>
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Grocery</th>
                                    <th>Current Stock</th>
                                    <th>Total Used</th>
                                    <th>Stock Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($slow_groceries ?? []) as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                                        <td><?= number_format((float)$row['current_stock'], 2); ?> <?= htmlspecialchars($row['unit'] ?? ''); ?></td>
                                        <td><?= number_format((float)$row['total_used'], 2); ?></td>
                                        <td>₱<?= number_format((float)$row['stock_value'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($slow_groceries)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No grocery movement data found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="reporting-card card shadow-sm border-0">
                    <div class="card-body table-responsive">
                        <h5 class="mb-3">Slow-Moving Products</h5>
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>On Hand</th>
                                    <th>Qty Sold</th>
                                    <th>Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($slow_products ?? []) as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['product_name']); ?></td>
                                        <td><?= number_format((float)$row['stock_quantity'], 2); ?></td>
                                        <td><?= number_format((float)$row['total_qty_sold'], 2); ?></td>
                                        <td>₱<?= number_format((float)$row['total_sales'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($slow_products)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No product movement data found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
