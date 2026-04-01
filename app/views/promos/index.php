<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Promo Codes</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="promo-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add Promo Code</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/promos/store">
                <div class="row g-3">
                    <div class="col-md-2"><label class="form-label">Code</label><input type="text" name="code" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Promo Name</label><input type="text" name="promo_name" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">Type</label><select name="discount_type" class="form-select"><option value="fixed">Fixed</option><option value="percent">Percent</option></select></div>
                    <div class="col-md-2"><label class="form-label">Value</label><input type="number" step="0.01" min="0" name="discount_value" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">Min Spend</label><input type="number" step="0.01" min="0" name="min_spend" class="form-control" value="0"></div>
                    <div class="col-md-1"><label class="form-label">Active</label><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked></div></div>
                    <div class="col-md-2"><label class="form-label">Start</label><input type="date" name="start_date" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">End</label><input type="date" name="end_date" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Usage Limit</label><input type="number" min="0" name="usage_limit" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Notes</label><input type="text" name="notes" class="form-control"></div>
                    <div class="col-md-12 d-grid"><button class="btn btn-dark">Save Promo</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="promo-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Discount</th>
                        <th>Min Spend</th>
                        <th>Usage</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($promos ?? []) as $promo): ?>
                        <?php
                            $status = $promo['promo_status'] ?? 'inactive';
                            $badge = 'secondary';
                            if ($status === 'active') $badge = 'success';
                            elseif (in_array($status, ['expired','used_up'], true)) $badge = 'danger';
                            elseif ($status === 'scheduled') $badge = 'warning';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($promo['code']); ?></td>
                            <td><?= htmlspecialchars($promo['promo_name']); ?></td>
                            <td><?= ($promo['discount_type'] === 'percent') ? number_format((float)$promo['discount_value'], 2) . '%' : '₱' . number_format((float)$promo['discount_value'], 2); ?></td>
                            <td>₱<?= number_format((float)$promo['min_spend'], 2); ?></td>
                            <td><?= (int)($promo['times_used'] ?? 0); ?> / <?= htmlspecialchars((string)($promo['usage_limit'] ?? '∞')); ?></td>
                            <td><span class="badge text-bg-<?= $badge; ?>"><?= strtoupper(htmlspecialchars($status)); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($promos)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No promo codes found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
