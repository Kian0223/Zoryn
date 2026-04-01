<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center auth-bg">
    <div class="card shadow-lg border-0 login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold mb-2">Zoryn Restaurant</h2>
                <p class="text-muted mb-0">Restaurant Inventory and Costing System</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= (require CONFIG_PATH . '/config.php')['base_url']; ?>/auth/login">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark w-100">Login</button>
            </form>

            <div class="mt-4 small text-muted text-center">
                Default login after import: <strong>admin / admin123</strong>
            </div>
        </div>
    </div>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
