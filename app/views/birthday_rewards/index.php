<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Birthday Rewards</h2>
        <a class="btn btn-dark" href="<?= $config['base_url']; ?>/customerexport/sms">Export SMS CSV</a>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="birthday-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Customers with Birthdays This Month</h5>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Birthdate</th>
                        <th>Points</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($birthday_customers ?? []) as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['full_name']); ?></td>
                            <td><?= htmlspecialchars($customer['phone'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($customer['email'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($customer['birthdate'] ?? '-'); ?></td>
                            <td><?= number_format((float)$customer['total_points'], 2); ?></td>
                            <td>₱<?= number_format((float)$customer['total_spent'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($birthday_customers)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No birthdays this month.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
