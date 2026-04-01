<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">HR Dashboard</h2></div>
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

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="hr-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Present</div><h3 class="mb-0"><?= (int)($attendance_summary['present_count'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="hr-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Late</div><h3 class="mb-0"><?= (int)($attendance_summary['late_count'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="hr-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Leave Requests</div><h3 class="mb-0"><?= (int)($leave_summary['total_requests'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="hr-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Overtime Hours</div><h3 class="mb-0"><?= number_format((float)($attendance_summary['total_overtime'] ?? 0), 2); ?></h3></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="hr-card card shadow-sm border-0">
                <div class="card-body table-responsive">
                    <h5 class="mb-3">Most Late Employees</h5>
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Employee</th><th>Code</th><th>Late Count</th></tr></thead>
                        <tbody>
                            <?php foreach (($late_employees ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['full_name']); ?></td>
                                <td><?= htmlspecialchars($row['employee_code']); ?></td>
                                <td><?= (int)$row['late_count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($late_employees)): ?><tr><td colspan="3" class="text-center text-muted">No late records found.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="hr-card card shadow-sm border-0">
                <div class="card-body table-responsive">
                    <h5 class="mb-3">Top Overtime Employees</h5>
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Employee</th><th>Code</th><th>OT Hours</th></tr></thead>
                        <tbody>
                            <?php foreach (($overtime_employees ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['full_name']); ?></td>
                                <td><?= htmlspecialchars($row['employee_code']); ?></td>
                                <td><?= number_format((float)$row['overtime_hours'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($overtime_employees)): ?><tr><td colspan="3" class="text-center text-muted">No overtime data found.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
