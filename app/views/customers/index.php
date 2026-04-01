<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Customers</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="customer-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Total Customers</div><h3 class="mb-0"><?= (int)($summary['total_customers'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="customer-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Customer Sales</div><h3 class="mb-0">₱<?= number_format((float)($summary['total_customer_sales'] ?? 0), 2); ?></h3></div></div></div>
        <div class="col-md-3"><div class="customer-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Outstanding Points</div><h3 class="mb-0"><?= number_format((float)($summary['total_points_outstanding'] ?? 0), 2); ?></h3></div></div></div>
        <div class="col-md-3"><div class="customer-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Avg Visits</div><h3 class="mb-0"><?= number_format((float)($summary['avg_visits'] ?? 0), 2); ?></h3></div></div></div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add Customer</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/customers/store">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Birthdate</label><input type="date" name="birthdate" class="form-control"></div>
                    <div class="col-md-12 d-grid"><button class="btn btn-dark">Save Customer</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Points</th>
                        <th>Total Spent</th>
                        <th>Visits</th>
                        <th width="170">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($customers ?? []) as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['customer_code']); ?></td>
                            <td><?= htmlspecialchars($customer['full_name']); ?></td>
                            <td>
                                <div><?= htmlspecialchars($customer['phone'] ?? '-'); ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($customer['email'] ?? '-'); ?></div>
                            </td>
                            <td><?= number_format((float)$customer['total_points'], 2); ?></td>
                            <td>₱<?= number_format((float)$customer['total_spent'], 2); ?></td>
                            <td><?= (int)$customer['visit_count']; ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCustomerModal<?= (int)$customer['id']; ?>">Edit</button>
                                <a class="btn btn-danger btn-sm" onclick="return confirm('Delete this customer?')" href="<?= $config['base_url']; ?>/customers/delete/<?= (int)$customer['id']; ?>">Del</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No customers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>

<?php foreach (($customers ?? []) as $customer): ?>
<div class="modal fade" id="editCustomerModal<?= (int)$customer['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0">
            <form method="POST" action="<?= $config['base_url']; ?>/customers/update/<?= (int)$customer['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($customer['full_name']); ?>" required></div>
                        <div class="col-md-2"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone'] ?? ''); ?>"></div>
                        <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email'] ?? ''); ?>"></div>
                        <div class="col-md-3"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($customer['address'] ?? ''); ?>"></div>
                        <div class="col-md-3"><label class="form-label">Birthdate</label><input type="date" name="birthdate" class="form-control" value="<?= htmlspecialchars($customer['birthdate'] ?? ''); ?>"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-dark">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
