<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Audit Trail</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="audit-card card shadow-sm border-0">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($logs ?? []) as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($log['created_at']))); ?></td>
                                <td><?= htmlspecialchars($log['module_name']); ?></td>
                                <td><?= htmlspecialchars($log['action_type']); ?></td>
                                <td><?= htmlspecialchars((string)($log['reference_id'] ?? '-')); ?></td>
                                <td><?= htmlspecialchars($log['description'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($log['full_name'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No audit logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
