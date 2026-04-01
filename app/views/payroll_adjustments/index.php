<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">Payroll Adjustments</h2></div>
    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="hr-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add Allowance / Deduction</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/payrolladjustments/store">
                <div class="row g-3">
                    <div class="col-md-2"><label class="form-label">Employee</label><select name="employee_id" class="form-select" required><?php foreach (($employees ?? []) as $e): ?><option value="<?= (int)$e['id']; ?>"><?= htmlspecialchars($e['full_name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label">Date</label><input type="date" name="adjustment_date" class="form-control" value="<?= date('Y-m-d'); ?>" required></div>
                    <div class="col-md-2"><label class="form-label">Type</label><select name="adjustment_type" class="form-select"><option value="allowance">Allowance</option><option value="deduction">Deduction</option></select></div>
                    <div class="col-md-3"><label class="form-label">Name</label><input type="text" name="adjustment_name" class="form-control" required></div>
                    <div class="col-md-1"><label class="form-label">Amount</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">Notes</label><input type="text" name="notes" class="form-control"></div>
                    <div class="col-md-12 d-grid"><button class="btn btn-dark">Save Adjustment</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="hr-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Date</th><th>Employee</th><th>Type</th><th>Name</th><th>Amount</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php foreach (($adjustments ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['adjustment_date']); ?></td>
                        <td><?= htmlspecialchars($row['full_name'] ?? '-'); ?></td>
                        <td><span class="badge text-bg-<?= $row['adjustment_type']==='allowance'?'success':'danger'; ?>"><?= strtoupper(htmlspecialchars($row['adjustment_type'])); ?></span></td>
                        <td><?= htmlspecialchars($row['adjustment_name']); ?></td>
                        <td>₱<?= number_format((float)$row['amount'], 2); ?></td>
                        <td><?= htmlspecialchars($row['notes'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($adjustments)): ?><tr><td colspan="6" class="text-center text-muted">No adjustments found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
