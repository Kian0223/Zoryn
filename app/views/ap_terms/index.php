<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">AP Terms</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add AP Term</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/apterms/store">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Term Name</label>
                        <input type="text" name="term_name" class="form-control" placeholder="e.g. Net 30" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- All Suppliers / General --</option>
                            <?php foreach (($suppliers ?? []) as $supplier): ?>
                                <option value="<?= (int)$supplier['id']; ?>"><?= htmlspecialchars($supplier['supplier_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Days Due</label>
                        <input type="number" min="0" name="days_due" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Default</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default">
                            <label class="form-check-label" for="is_default">Set as default</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-grid">
                        <label class="form-label opacity-0">Save</label>
                        <button class="btn btn-dark">Save Term</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Supplier Scope</th>
                        <th>Days Due</th>
                        <th>Default</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($terms ?? []) as $term): ?>
                        <tr>
                            <td><?= htmlspecialchars($term['term_name']); ?></td>
                            <td><?= htmlspecialchars($term['supplier_name'] ?? 'General'); ?></td>
                            <td><?= (int)$term['days_due']; ?></td>
                            <td><span class="badge text-bg-<?= !empty($term['is_default']) ? 'success' : 'secondary'; ?>"><?= !empty($term['is_default']) ? 'YES' : 'NO'; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($terms)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No AP terms found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
