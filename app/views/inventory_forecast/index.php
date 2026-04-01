<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Inventory Forecast</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="forecast-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Forecast Days</label>
                    <input type="number" min="7" max="180" name="days" class="form-control" value="<?= (int)$days; ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-dark">Refresh Forecast</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="forecast-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Items to Reorder</div><h3 class="mb-0"><?= (int)($summary['items_to_reorder'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-4"><div class="forecast-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Urgent Items</div><h3 class="mb-0 text-danger"><?= (int)($summary['urgent_items'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-4"><div class="forecast-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Estimated Restock Cost</div><h3 class="mb-0">₱<?= number_format((float)($summary['estimated_restock_cost'] ?? 0), 2); ?></h3></div></div></div>
    </div>

    <div class="forecast-card card shadow-sm border-0 mb-4">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Reorder Suggestions</h5>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Grocery</th>
                        <th>Current Stock</th>
                        <th>Avg Daily Usage</th>
                        <th>Days Left</th>
                        <th>Reorder Point</th>
                        <th>Suggested Qty</th>
                        <th>Est. Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($reorder_rows ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                        <td><?= number_format((float)$row['current_stock'], 2); ?> <?= htmlspecialchars($row['unit'] ?? ''); ?></td>
                        <td><?= number_format((float)$row['avg_daily_usage'], 2); ?></td>
                        <td><?= $row['days_left_estimate'] !== null ? number_format((float)$row['days_left_estimate'], 2) : '-'; ?></td>
                        <td><?= number_format((float)$row['effective_reorder_point'], 2); ?></td>
                        <td><?= number_format((float)$row['suggested_order_qty'], 2); ?></td>
                        <td>₱<?= number_format((float)$row['estimated_order_cost'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reorder_rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No reorder suggestions right now.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="forecast-card card shadow-sm border-0 mb-4">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Forecast and Planning Values</h5>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Grocery</th>
                        <th>Usage 30d</th>
                        <th>Current Stock</th>
                        <th>Planning</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($forecast_rows ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                        <td><?= number_format((float)$row['total_used_last_30'], 2); ?></td>
                        <td><?= number_format((float)$row['current_stock'], 2); ?> <?= htmlspecialchars($row['unit'] ?? ''); ?></td>
                        <td>
                            <form method="POST" action="<?= $config['base_url']; ?>/inventoryforecast/updateGroceryPlanning/<?= (int)$row['id']; ?>" class="row g-2">
                                <div class="col-md-3"><input type="number" step="0.01" min="0" name="reorder_point" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$row['reorder_point']); ?>" placeholder="ROP"></div>
                                <div class="col-md-3"><input type="number" step="0.01" min="0" name="reorder_quantity" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$row['reorder_quantity']); ?>" placeholder="ROQ"></div>
                                <div class="col-md-3"><input type="number" step="0.01" min="0" name="safety_stock" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$row['safety_stock']); ?>" placeholder="Safety"></div>
                                <div class="col-md-3 d-grid"><button class="btn btn-dark btn-sm">Save</button></div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($forecast_rows)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No forecast data found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="forecast-card card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-3">Supplier Purchase Planning</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/inventoryforecast/saveSupplierLink" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Select Supplier --</option>
                        <?php foreach (($suppliers ?? []) as $supplier): ?>
                        <option value="<?= (int)$supplier['id']; ?>"><?= htmlspecialchars($supplier['supplier_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Grocery</label>
                    <select name="grocery_id" class="form-select" required>
                        <option value="">-- Select Grocery --</option>
                        <?php foreach (($groceries ?? []) as $grocery): ?>
                        <option value="<?= (int)$grocery['id']; ?>"><?= htmlspecialchars($grocery['grocery_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Lead Time Days</label>
                    <input type="number" min="0" name="lead_time_days" class="form-control" value="3">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Last Cost</label>
                    <input type="number" step="0.01" min="0" name="last_cost" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Preferred</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="preferred_flag" value="1" class="form-check-input" id="preferred_flag">
                        <label class="form-check-label" for="preferred_flag">Yes</label>
                    </div>
                </div>
                <div class="col-md-10">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control">
                </div>
                <div class="col-md-2 d-grid">
                    <label class="form-label opacity-0">Save</label>
                    <button class="btn btn-dark">Save Link</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Grocery</th>
                            <th>Lead Time</th>
                            <th>Preferred</th>
                            <th>Last Cost</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($supplier_links ?? []) as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                            <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                            <td><?= (int)$row['lead_time_days']; ?> days</td>
                            <td><span class="badge text-bg-<?= !empty($row['preferred_flag']) ? 'success' : 'secondary'; ?>"><?= !empty($row['preferred_flag']) ? 'YES' : 'NO'; ?></span></td>
                            <td>₱<?= number_format((float)$row['last_cost'], 2); ?></td>
                            <td><?= htmlspecialchars($row['notes'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($supplier_links)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No supplier planning links found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
