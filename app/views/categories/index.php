<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Categories</h2>
        </div>
        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Add Category</h5>
                        <form method="POST" action="<?= $config['base_url']; ?>/categories/store">
                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" name="category_name" class="form-control" required>
                            </div>
                            <button class="btn btn-dark w-100">Save Category</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr><th>#</th><th>Category</th><th width="280">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $index => $category): ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td><?= htmlspecialchars($category['category_name']); ?></td>
                                        <td>
                                            <form class="d-flex gap-2" method="POST" action="<?= $config['base_url']; ?>/categories/update/<?= $category['id']; ?>">
                                                <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($category['category_name']); ?>" required>
                                                <button class="btn btn-primary btn-sm">Update</button>
                                                <a class="btn btn-danger btn-sm" onclick="return confirm('Delete this category?')" href="<?= $config['base_url']; ?>/categories/delete/<?= $category['id']; ?>">Delete</a>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="3" class="text-center text-muted">No categories found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
