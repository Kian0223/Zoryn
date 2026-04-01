<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">Leave Requests</h2></div>
    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="hr-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Submit Leave Request</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/leaves/store">
                <div class="row g-3">
                    <div class="col-md-2"><label class="form-label">Employee</label><select name="employee_id" class="form-select" required><?php foreach (($employees ?? []) as $e): ?><option value="<?= (int)$e['id']; ?>"><?= htmlspecialchars($e['full_name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label">Type</label><select name="leave_type" class="form-select"><option value="vacation">Vacation</option><option value="sick">Sick</option><option value="emergency">Emergency</option><option value="unpaid">Unpaid</option><option value="other">Other</option></select></div>
                    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="date_from" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="date_to" class="form-control" required></div>
                    <div class="col-md-1"><label class="form-label">Days</label><input type="number" step="0.5" min="0.5" name="days_count" class="form-control" value="1"></div>
                    <div class="col-md-3"><label class="form-label">Reason</label><input type="text" name="reason" class="form-control"></div>
                    <div class="col-md-12 d-grid"><button class="btn btn-dark">Submit Request</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="hr-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Employee</th><th>Type</th><th>Date Range</th><th>Days</th><th>Status</th><th>Reason</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach (($leave_requests ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name'] ?? '-'); ?></td>
                        <td><?= strtoupper(htmlspecialchars($row['leave_type'])); ?></td>
                        <td><?= htmlspecialchars($row['date_from']); ?> to <?= htmlspecialchars($row['date_to']); ?></td>
                        <td><?= number_format((float)$row['days_count'], 2); ?></td>
                        <td><span class="badge text-bg-<?= $row['status']==='approved'?'success':($row['status']==='rejected'?'danger':'warning'); ?>"><?= strtoupper(htmlspecialchars($row['status'])); ?></span></td>
                        <td><?= htmlspecialchars($row['reason'] ?? '-'); ?></td>
                        <td>
                            <?php if (($row['status'] ?? '') === 'pending'): ?>
                                <a class="btn btn-success btn-sm" href="<?= $config['base_url']; ?>/leaves/approve/<?= (int)$row['id']; ?>">Approve</a>
                                <a class="btn btn-danger btn-sm" href="<?= $config['base_url']; ?>/leaves/reject/<?= (int)$row['id']; ?>">Reject</a>
                            <?php else: ?>
                                <span class="text-muted small">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leave_requests)): ?><tr><td colspan="7" class="text-center text-muted">No leave requests found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
