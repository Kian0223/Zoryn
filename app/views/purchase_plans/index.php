<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Purchase Planning</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="planning-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Total Plans</div><h3 class="mb-0"><?= (int)($summary['total_plans'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="planning-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Draft</div><h3 class="mb-0"><?= (int)($summary['draft_plans'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="planning-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Submitted</div><h3 class="mb-0"><?= (int)($summary['submitted_plans'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="planning-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Approved</div><h3 class="mb-0"><?= (int)($summary['approved_plans'] ?? 0); ?></h3></div></div></div>
    </div>

    <div class="planning-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Generate Plan from Reorder List</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/purchaseplans/createFromReorder" class="row g-3">
                <div class="col-md-10">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Optional planning notes">
                </div>
                <div class="col-md-2 d-grid">
                    <label class="form-label opacity-0">Generate</label>
                    <button class="btn btn-dark">Generate Plan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="planning-card card shadow-sm border-0 mb-4">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Purchase Plans</h5>
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Plan No</th><th>Date</th><th>Status</th><th>Items</th><th>Notes</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach (($plans ?? []) as $plan): ?>
                    <tr>
                        <td><?= htmlspecialchars($plan['plan_no']); ?></td>
                        <td><?= htmlspecialchars($plan['plan_date']); ?></td>
                        <td><span class="badge text-bg-<?= $plan['status']==='approved'?'success':($plan['status']==='rejected'?'danger':($plan['status']==='submitted'?'warning':'secondary')); ?>"><?= strtoupper(htmlspecialchars($plan['status'])); ?></span></td>
                        <td>
                            <?php foreach (($plan['items'] ?? []) as $item): ?>
                                <div class="mb-2">
                                    <strong><?= htmlspecialchars($item['grocery_name']); ?></strong><br>
                                    <form method="POST" action="<?= $config['base_url']; ?>/purchaseplans/updateItem/<?= (int)$item['id']; ?>" class="row g-2">
                                        <div class="col-md-3"><input type="number" step="0.01" min="0" name="approved_qty" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$item['approved_qty']); ?>"></div>
                                        <div class="col-md-3"><input type="number" step="0.01" min="0" name="unit_cost" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$item['unit_cost']); ?>"></div>
                                        <div class="col-md-4">
                                            <select name="supplier_id" class="form-select form-select-sm">
                                                <option value="">-- Supplier --</option>
                                                <?php foreach (($suppliers ?? []) as $supplier): ?>
                                                <option value="<?= (int)$supplier['id']; ?>" <?= ((int)($item['supplier_id'] ?? 0) === (int)$supplier['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($supplier['supplier_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-sm">Save</button></div>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td><?= htmlspecialchars($plan['notes'] ?? '-'); ?></td>
                        <td>
                            <?php if (($plan['status'] ?? '') === 'draft'): ?>
                                <a class="btn btn-warning btn-sm" href="<?= $config['base_url']; ?>/purchaseplans/submit/<?= (int)$plan['id']; ?>">Submit</a>
                            <?php elseif (($plan['status'] ?? '') === 'submitted'): ?>
                                <a class="btn btn-success btn-sm" href="<?= $config['base_url']; ?>/purchaseplans/approve/<?= (int)$plan['id']; ?>">Approve</a>
                                <a class="btn btn-danger btn-sm" href="<?= $config['base_url']; ?>/purchaseplans/reject/<?= (int)$plan['id']; ?>">Reject</a>
                            <?php else: ?>
                                <span class="text-muted small">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($plans)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No purchase plans found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="planning-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add Supplier Quotation</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/supplierquotations/store" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Grocery</label>
                    <select name="grocery_id" class="form-select" required>
                        <option value="">-- Select Grocery --</option>
                        <?php foreach (($groceries ?? []) as $grocery): ?>
                        <option value="<?= (int)$grocery['id']; ?>"><?= htmlspecialchars($grocery['grocery_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Select Supplier --</option>
                        <?php foreach (($suppliers ?? []) as $supplier): ?>
                        <option value="<?= (int)$supplier['id']; ?>"><?= htmlspecialchars($supplier['supplier_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Quoted Price</label><input type="number" step="0.01" min="0" name="quoted_price" class="form-control" required></div>
                <div class="col-md-1"><label class="form-label">Lead Time</label><input type="number" min="0" name="lead_time_days" class="form-control" value="0"></div>
                <div class="col-md-1"><label class="form-label">MOQ</label><input type="number" step="0.01" min="0" name="min_order_qty" class="form-control" value="0"></div>
                <div class="col-md-2"><label class="form-label">Quote Date</label><input type="date" name="quote_date" class="form-control" value="<?= date('Y-m-d'); ?>"></div>
                <div class="col-md-10"><label class="form-label">Notes</label><input type="text" name="notes" class="form-control"></div>
                <div class="col-md-2 d-grid"><label class="form-label opacity-0">Save</label><button class="btn btn-dark">Save Quote</button></div>
            </form>
        </div>
    </div>

    <div class="planning-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Supplier Quotation Comparison</h5>
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Grocery</th><th>Supplier</th><th>Quoted Price</th><th>Lead Time</th><th>MOQ</th><th>Quote Date</th><th>Best?</th></tr>
                </thead>
                <tbody>
                    <?php foreach (($quotation_rows ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                        <td>₱<?= number_format((float)$row['quoted_price'], 2); ?></td>
                        <td><?= (int)$row['lead_time_days']; ?> days</td>
                        <td><?= number_format((float)$row['min_order_qty'], 2); ?></td>
                        <td><?= htmlspecialchars($row['quote_date']); ?></td>
                        <td><span class="badge text-bg-<?= ((int)$row['quote_rank'] === 1) ? 'success' : 'secondary'; ?>"><?= ((int)$row['quote_rank'] === 1) ? 'BEST' : '-'; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($quotation_rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No supplier quotations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
