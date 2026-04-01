<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Cashier Shifts</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="shift-stat-card card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Today's Cash Sales</div>
                        <h3 class="mb-0">₱<?= number_format((float)($today_cash_sales ?? 0), 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="shift-stat-card card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Shift Status</div>
                        <h3 class="mb-0"><?= !empty($open_shift) ? 'OPEN' : 'NO OPEN SHIFT'; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($open_shift)): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Open Shift</h5>
                    <form method="POST" action="<?= $config['base_url']; ?>/cashiershifts/open">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Opening Cash</label>
                                <input type="number" step="0.01" min="0" name="opening_cash" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-md-3 d-grid">
                                <label class="form-label opacity-0">Save</label>
                                <button class="btn btn-dark">Open Shift</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Close Shift</h5>
                    <div class="mb-3 text-muted">
                        Opening Cash: ₱<?= number_format((float)$open_shift['opening_cash'], 2); ?>
                    </div>
                    <form method="POST" action="<?= $config['base_url']; ?>/cashiershifts/close/<?= (int)$open_shift['id']; ?>">
                        <input type="hidden" name="opening_cash_hidden" value="<?= htmlspecialchars((float)$open_shift['opening_cash']); ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Closing Cash</label>
                                <input type="number" step="0.01" min="0" name="closing_cash" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-md-3 d-grid">
                                <label class="form-label opacity-0">Save</label>
                                <button class="btn btn-danger">Close Shift</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <h5 class="mb-3">Recent Shifts</h5>
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Cashier</th>
                            <th>Date</th>
                            <th>Opening</th>
                            <th>Closing</th>
                            <th>Expected</th>
                            <th>Difference</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($recent_shifts ?? []) as $shift): ?>
                            <tr>
                                <td><?= htmlspecialchars($shift['full_name'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($shift['shift_date']); ?></td>
                                <td>₱<?= number_format((float)$shift['opening_cash'], 2); ?></td>
                                <td>₱<?= number_format((float)($shift['closing_cash'] ?? 0), 2); ?></td>
                                <td>₱<?= number_format((float)($shift['expected_cash'] ?? 0), 2); ?></td>
                                <td>₱<?= number_format((float)($shift['cash_difference'] ?? 0), 2); ?></td>
                                <td><span class="badge text-bg-<?= ($shift['status'] === 'open') ? 'success' : 'secondary'; ?>"><?= strtoupper(htmlspecialchars($shift['status'])); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_shifts)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No shifts found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
