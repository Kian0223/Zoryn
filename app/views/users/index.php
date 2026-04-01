<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">Users</h2></div>
        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Add User</h5>
                <form method="POST" action="<?= $config['base_url']; ?>/users/store">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
                        <div class="col-md-2"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                        <div class="col-md-2"><label class="form-label">Role</label><select name="role" class="form-select"><option value="admin">Admin</option><option value="cashier">Cashier</option><option value="staff">Staff</option></select></div>
                        <div class="col-md-1 d-grid"><label class="form-label opacity-0">Save</label><button class="btn btn-dark">Save</button></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Created</th><th width="430">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['full_name']); ?></td>
                                <td><?= htmlspecialchars($user['username']); ?></td>
                                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($user['role']); ?></span></td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?></td>
                                <td>
                                    <form class="row g-2" method="POST" action="<?= $config['base_url']; ?>/users/update/<?= $user['id']; ?>">
                                        <div class="col-md-4"><input type="text" name="full_name" class="form-control form-control-sm" value="<?= htmlspecialchars($user['full_name']); ?>"></div>
                                        <div class="col-md-3"><input type="text" name="username" class="form-control form-control-sm" value="<?= htmlspecialchars($user['username']); ?>"></div>
                                        <div class="col-md-2"><input type="password" name="password" class="form-control form-control-sm" placeholder="Keep blank"></div>
                                        <div class="col-md-2"><select name="role" class="form-select form-select-sm"><option value="admin" <?= $user['role']==='admin'?'selected':''; ?>>Admin</option><option value="cashier" <?= $user['role']==='cashier'?'selected':''; ?>>Cashier</option><option value="staff" <?= $user['role']==='staff'?'selected':''; ?>>Staff</option></select></div>
                                        <div class="col-md-1 d-flex gap-1">
                                            <button class="btn btn-primary btn-sm">Save</button>
                                            <a class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')" href="<?= $config['base_url']; ?>/users/delete/<?= $user['id']; ?>">Del</a>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
