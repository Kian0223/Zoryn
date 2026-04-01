<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">Employees</h2></div>
    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="hr-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add Employee</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/employees/store">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">Linked User</label><select name="user_id" class="form-select"><option value="">-- Optional --</option><?php foreach (($users ?? []) as $u): ?><option value="<?= (int)$u['id']; ?>"><?= htmlspecialchars($u['full_name'] ?? $u['username'] ?? ('User ' . $u['id'])); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label">Role</label><select name="role_id" class="form-select"><?php foreach (($roles ?? []) as $r): ?><option value="<?= (int)$r['id']; ?>"><?= htmlspecialchars($r['role_name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label">Job Title</label><input type="text" name="job_title" class="form-control"></div>
                    <div class="col-md-1"><label class="form-label">Daily</label><input type="number" step="0.01" min="0" name="daily_rate" class="form-control" value="0"></div>
                    <div class="col-md-1"><label class="form-label">Hourly</label><input type="number" step="0.01" min="0" name="hourly_rate" class="form-control" value="0"></div>
                    <div class="col-md-1"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <div class="col-md-2"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control"></div>
                    <div class="col-md-12 d-grid"><button class="btn btn-dark">Save Employee</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="hr-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Code</th><th>Name</th><th>Role</th><th>Job Title</th><th>Rate</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach (($employees ?? []) as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['employee_code']); ?></td>
                        <td><?= htmlspecialchars($e['full_name']); ?></td>
                        <td><?= htmlspecialchars($e['role_name'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($e['job_title'] ?? '-'); ?></td>
                        <td>₱<?= number_format((float)$e['hourly_rate'], 2); ?>/hr</td>
                        <td><span class="badge text-bg-<?= ($e['status'] === 'active') ? 'success' : 'secondary'; ?>"><?= strtoupper(htmlspecialchars($e['status'])); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($employees)): ?><tr><td colspan="6" class="text-center text-muted">No employees found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
