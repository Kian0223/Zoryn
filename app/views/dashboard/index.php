<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php $config = require CONFIG_PATH . '/config.php'; ?>
<div class="zoryn-admin-shell">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>

    <main class="zoryn-main-content">
        <section class="zoryn-top-hero mb-4">
            <div class="zoryn-top-hero-overlay"></div>
            <div class="zoryn-top-hero-content">
                <div>
                    <div class="zoryn-hero-kicker">Zoryn Restaurant Management System</div>
                    <h1 class="zoryn-page-title">Dashboard Overview</h1>
                    <p class="zoryn-page-subtitle mb-0">
                        Welcome back, <?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'User'); ?>. Monitor daily sales, products, groceries, expenses, and low-stock alerts in one place.
                    </p>
                </div>
                <div class="zoryn-role-pill">
                    <i class="bi bi-shield-check"></i>
                    <span><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Staff'); ?></span>
                </div>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="zoryn-stat-card">
                    <div class="zoryn-stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div class="zoryn-stat-label">Month Sales</div>
                    <div class="zoryn-stat-value">₱<?= number_format((float)$stats['month_sales'], 2); ?></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="zoryn-stat-card">
                    <div class="zoryn-stat-icon"><i class="bi bi-receipt-cutoff"></i></div>
                    <div class="zoryn-stat-label">Month Expenses</div>
                    <div class="zoryn-stat-value">₱<?= number_format((float)$stats['month_expenses'], 2); ?></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="zoryn-stat-card">
                    <div class="zoryn-stat-icon"><i class="bi bi-basket2-fill"></i></div>
                    <div class="zoryn-stat-label">Grocery Low Stock</div>
                    <div class="zoryn-stat-value"><?= (int)$stats['grocery_low_stock']; ?></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="zoryn-stat-card zoryn-stat-card-highlight">
                    <div class="zoryn-stat-icon"><i class="bi bi-box-seam-fill"></i></div>
                    <div class="zoryn-stat-label">Product Low Stock</div>
                    <div class="zoryn-stat-value"><?= (int)$stats['product_low_stock']; ?></div>
                </div>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="zoryn-panel h-100">
                    <div class="zoryn-panel-header">
                        <div>
                            <div class="zoryn-panel-kicker">Operations</div>
                            <h5 class="mb-0">Recent Sales</h5>
                        </div>
                        <a href="<?= $config['base_url']; ?>/reports/index" class="zoryn-btn-outline">Open Report</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table zoryn-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Date</th>
                                    <th>Cashier</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_sales as $sale): ?>
                                    <tr>
                                        <td><span class="zoryn-table-pill"><?= htmlspecialchars($sale['receipt_no']); ?></span></td>
                                        <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($sale['sale_date']))); ?></td>
                                        <td><?= htmlspecialchars($sale['cashier_name'] ?? '-'); ?></td>
                                        <td class="fw-semibold text-warning-emphasis">₱<?= number_format((float)$sale['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_sales)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">No recent sales yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="zoryn-panel h-100">
                    <div class="zoryn-panel-header mb-0">
                        <div>
                            <div class="zoryn-panel-kicker">Quick Summary</div>
                            <h5 class="mb-0">System Snapshot</h5>
                        </div>
                    </div>

                    <div class="zoryn-summary-list mt-3">
                        <div class="zoryn-summary-item">
                            <span>Total Receipts</span>
                            <strong><?= (int)$stats['total_receipts']; ?></strong>
                        </div>
                        <div class="zoryn-summary-item">
                            <span>Products</span>
                            <strong><?= (int)$stats['total_products']; ?></strong>
                        </div>
                        <div class="zoryn-summary-item">
                            <span>Viands</span>
                            <strong><?= (int)$stats['total_viands']; ?></strong>
                        </div>
                        <div class="zoryn-summary-item">
                            <span>Users</span>
                            <strong><?= (int)$stats['total_users']; ?></strong>
                        </div>
                    </div>

                    <div class="zoryn-action-box mt-4">
                        <div class="zoryn-action-title">Quick Actions</div>
                        <div class="d-grid gap-2 mt-3">
                            <a href="<?= $config['base_url']; ?>/sales/index" class="zoryn-btn-gold text-center">Go to POS</a>
                            <a href="<?= $config['base_url']; ?>/expenses/index" class="zoryn-btn-outline text-center">Manage Expenses</a>
                            <a href="<?= $config['base_url']; ?>/inventory/index" class="zoryn-btn-outline text-center">View Inventory</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row g-4">
            <div class="col-12">
                <div class="zoryn-panel">
                    <div class="zoryn-panel-header">
                        <div>
                            <div class="zoryn-panel-kicker">Performance</div>
                            <h5 class="mb-0">Top Selling Items This Month</h5>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table zoryn-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Qty Sold</th>
                                    <th>Revenue</th>
                                    <th>Avg Price</th>
                                    <th>Profit / Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['item_name']); ?></td>
                                        <td><?= htmlspecialchars($item['item_type']); ?></td>
                                        <td><?= number_format((float)$item['qty_sold'], 2); ?></td>
                                        <td>₱<?= number_format((float)$item['revenue'], 2); ?></td>
                                        <td>₱<?= number_format((float)$item['avg_price'], 2); ?></td>
                                        <td class="<?= (float)$item['profit_per_unit'] >= 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold'; ?>">
                                            ₱<?= number_format((float)$item['profit_per_unit'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($top_items)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">No sales data available yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
