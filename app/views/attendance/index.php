<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">Attendance</h2></div>
    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="hr-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Log Attendance</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/attendance/store">
                <div class="row g-3">
                    <div class="col-md-2"><label class="form-label">Employee</label><select name="employee_id" class="form-select" required><?php foreach (($employees ?? []) as $e): ?><option value="<?= (int)$e['id']; ?>"><?= htmlspecialchars($e['full_name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label">Date</label><input type="date" name="attendance_date" class="form-control" value="<?= date('Y-m-d'); ?>" required></div>
                    <div class="col-md-2"><label class="form-label">Time In</label><input type="datetime-local" name="time_in" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Time Out</label><input type="datetime-local" name="time_out" class="form-control"></div>
                    <div class="col-md-1"><label class="form-label">Hours</label><input type="number" step="0.01" min="0" name="hours_worked" class="form-control" value="8"></div>
                    <div class="col-md-1"><label class="form-label">OT</label><input type="number" step="0.01" min="0" name="overtime_hours" class="form-control" value="0"></div>
                    <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="present">Present</option><option value="late">Late</option><option value="absent">Absent</option><option value="half_day">Half Day</option><option value="leave">Leave</option></select></div>
                    <div class="col-md-12"><label class="form-label">Notes</label><input type="text" name="notes" class="form-control"></div>
                    <div class="col-md-12 d-grid"><button class="btn btn-dark">Save Attendance</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="hr-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Date</th><th>Employee</th><th>Time In</th><th>Time Out</th><th>Hours</th><th>OT</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach (($attendance_rows ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['attendance_date']); ?></td>
                        <td><?= htmlspecialchars($row['full_name'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['time_in'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['time_out'] ?? '-'); ?></td>
                        <td><?= number_format((float)$row['hours_worked'], 2); ?></td>
                        <td><?= number_format((float)$row['overtime_hours'], 2); ?></td>
                        <td><span class="badge text-bg-secondary"><?= strtoupper(str_replace('_', ' ', htmlspecialchars($row['status']))); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($attendance_rows)): ?><tr><td colspan="7" class="text-center text-muted">No attendance records found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
