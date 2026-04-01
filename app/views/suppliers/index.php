<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Suppliers</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Balance Due</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($suppliers ?? []) as $supplier): ?>
                        <tr>
                            <td><?= htmlspecialchars($supplier['supplier_name']); ?></td>
                            <td><?= htmlspecialchars($supplier['contact_person'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($supplier['phone'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($supplier['email'] ?? '-'); ?></td>
                            <td>₱<?= number_format((float)($supplier['total_balance_due'] ?? 0), 2); ?></td>
                            <td><?= htmlspecialchars($supplier['notes'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($suppliers)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No suppliers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
