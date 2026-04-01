<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Expenses</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add Expense</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/expenses/store">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Expense Date</label>
                        <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="General">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Supplier Tag</label>
                        <select name="supplier_tag" class="form-select">
                            <option value="">-- Optional Supplier --</option>
                            <?php foreach (($suppliers ?? []) as $supplier): ?>
                                <option value="<?= htmlspecialchars($supplier['supplier_name']); ?>"><?= htmlspecialchars($supplier['supplier_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                    <div class="col-md-3 d-grid">
                        <label class="form-label opacity-0">Save</label>
                        <button class="btn btn-dark">Save Expense</button>
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
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($expenses ?? []) as $expense): ?>
                        <tr>
                            <td><?= htmlspecialchars($expense['expense_date']); ?></td>
                            <td><?= htmlspecialchars($expense['description']); ?></td>
                            <td><?= htmlspecialchars($expense['category'] ?? '-'); ?></td>
                            <td>₱<?= number_format((float)$expense['amount'], 2); ?></td>
                            <td><?= htmlspecialchars($expense['notes'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No expenses found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
