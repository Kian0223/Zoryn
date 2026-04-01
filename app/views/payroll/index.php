<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">Payroll Summary</h2></div>
    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="hr-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3"><label class="form-label">From</label><input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from); ?>"></div>
                <div class="col-md-3"><label class="form-label">To</label><input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to); ?>"></div>
                <div class="col-md-2 d-grid"><label class="form-label opacity-0">Filter</label><button class="btn btn-dark">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="hr-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Employee</th><th>Hours</th><th>OT Hours</th><th>Regular Pay</th><th>OT Pay</th><th>Allowances</th><th>Deductions</th><th>Net Pay</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']); ?></td>
                        <td><?= number_format((float)$row['total_hours'], 2); ?></td>
                        <td><?= number_format((float)$row['overtime_hours'], 2); ?></td>
                        <td>₱<?= number_format((float)$row['regular_pay'], 2); ?></td>
                        <td>₱<?= number_format((float)$row['overtime_pay'], 2); ?></td>
                        <td>₱<?= number_format((float)$row['total_allowances'], 2); ?></td>
                        <td>₱<?= number_format((float)$row['total_deductions'], 2); ?></td>
                        <td><strong>₱<?= number_format((float)$row['net_pay'], 2); ?></strong></td>
                        <td><a class="btn btn-outline-secondary btn-sm" target="_blank" href="<?= $config['base_url']; ?>/payroll/payslip/<?= (int)$row['id']; ?>?date_from=<?= urlencode($date_from); ?>&date_to=<?= urlencode($date_to); ?>">Payslip</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?><tr><td colspan="9" class="text-center text-muted">No payroll data found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
