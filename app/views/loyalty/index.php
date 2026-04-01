<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Loyalty Analytics</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="loyalty-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Total Customers</div><h3 class="mb-0"><?= (int)($summary['total_customers'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="loyalty-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Customer Sales</div><h3 class="mb-0">₱<?= number_format((float)($summary['total_customer_sales'] ?? 0), 2); ?></h3></div></div></div>
        <div class="col-md-3"><div class="loyalty-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Points Outstanding</div><h3 class="mb-0"><?= number_format((float)($summary['total_points_outstanding'] ?? 0), 2); ?></h3></div></div></div>
        <div class="col-md-3"><div class="loyalty-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Avg Visits</div><h3 class="mb-0"><?= number_format((float)($summary['avg_visits'] ?? 0), 2); ?></h3></div></div></div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Repeat Customers</h5>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Visits</th>
                        <th>Total Spent</th>
                        <th>Points</th>
                        <th width="280">Adjust Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($repeat_customers ?? []) as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['full_name']); ?></td>
                            <td>
                                <div><?= htmlspecialchars($customer['phone'] ?? '-'); ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($customer['email'] ?? '-'); ?></div>
                            </td>
                            <td><?= (int)$customer['visit_count']; ?></td>
                            <td>₱<?= number_format((float)$customer['total_spent'], 2); ?></td>
                            <td><?= number_format((float)$customer['total_points'], 2); ?></td>
                            <td>
                                <form method="POST" action="<?= $config['base_url']; ?>/loyalty/adjust/<?= (int)$customer['id']; ?>" class="d-flex gap-2">
                                    <select name="transaction_type" class="form-select form-select-sm">
                                        <option value="adjust">Add</option>
                                        <option value="redeem">Redeem</option>
                                    </select>
                                    <input type="number" step="0.01" min="0.01" name="points" class="form-control form-control-sm" placeholder="Points" required>
                                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Note">
                                    <button class="btn btn-dark btn-sm">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($repeat_customers)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No customer analytics found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
