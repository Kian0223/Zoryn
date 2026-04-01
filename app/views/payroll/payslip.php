<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="container py-4">
    <div class="hr-card card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h2 class="mb-1">Payslip</h2>
                    <div class="text-muted"><?= htmlspecialchars($date_from); ?> to <?= htmlspecialchars($date_to); ?></div>
                </div>
                <button class="btn btn-dark" onclick="window.print()">Print</button>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div><strong>Employee:</strong> <?= htmlspecialchars($employee['full_name']); ?></div>
                    <div><strong>Code:</strong> <?= htmlspecialchars($employee['employee_code']); ?></div>
                    <div><strong>Position:</strong> <?= htmlspecialchars($employee['job_title'] ?? '-'); ?></div>
                </div>
                <div class="col-md-6">
                    <div><strong>Total Hours:</strong> <?= number_format((float)$employee['total_hours'], 2); ?></div>
                    <div><strong>Overtime Hours:</strong> <?= number_format((float)$employee['overtime_hours'], 2); ?></div>
                    <div><strong>Hourly Rate:</strong> ₱<?= number_format((float)$employee['hourly_rate'], 2); ?></div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <tbody>
                        <tr><th>Regular Pay</th><td>₱<?= number_format((float)$employee['regular_pay'], 2); ?></td></tr>
                        <tr><th>Overtime Pay</th><td>₱<?= number_format((float)$employee['overtime_pay'], 2); ?></td></tr>
                        <tr><th>Allowances</th><td>₱<?= number_format((float)$allowances, 2); ?></td></tr>
                        <tr><th>Deductions</th><td>₱<?= number_format((float)$deductions, 2); ?></td></tr>
                        <tr><th>Net Pay</th><td><strong>₱<?= number_format((float)$employee['net_pay'], 2); ?></strong></td></tr>
                    </tbody>
                </table>
            </div>

            <h5 class="mb-3">Adjustments</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Date</th><th>Type</th><th>Name</th><th>Amount</th><th>Notes</th></tr></thead>
                    <tbody>
                        <?php foreach (($adjustments ?? []) as $adj): ?>
                        <tr>
                            <td><?= htmlspecialchars($adj['adjustment_date']); ?></td>
                            <td><?= strtoupper(htmlspecialchars($adj['adjustment_type'])); ?></td>
                            <td><?= htmlspecialchars($adj['adjustment_name']); ?></td>
                            <td>₱<?= number_format((float)$adj['amount'], 2); ?></td>
                            <td><?= htmlspecialchars($adj['notes'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($adjustments)): ?><tr><td colspan="5" class="text-center text-muted">No adjustments found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
