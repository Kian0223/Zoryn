<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Groceries Purchase Monitoring</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Grocery Items</div>
                        <h3 class="mb-0"><?= count($groceries ?? []); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Low Stock Items</div>
                        <h3 class="mb-0 text-warning"><?= (int)($low_stock_count ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Out of Stock</div>
                        <h3 class="mb-0 text-danger"><?= (int)($out_of_stock_count ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Add Grocery Item</h5>

                <form method="POST" action="<?= $config['base_url']; ?>/groceries/store">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Grocery Name</label>
                            <input type="text" name="grocery_name" class="form-control" required>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control" value="pcs" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Package Count</label>
                            <input type="number" step="0.01" min="0.01" name="package_count" id="add_package_count" class="form-control" value="1" required oninput="computeAddGroceryValues()">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Package Quantity</label>
                            <input type="number" step="0.01" min="0.01" name="package_quantity" id="add_package_quantity" class="form-control" value="1" required oninput="computeAddGroceryValues()">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Package Cost</label>
                            <input type="number" step="0.01" min="0" name="package_cost" id="add_package_cost" class="form-control" value="0" required oninput="computeAddGroceryValues()">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Current Stock (Auto)</label>
                            <input type="text" id="add_current_stock_preview" class="form-control" value="1.00" readonly>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Low Stock Alert</label>
                            <input type="number" step="0.01" min="0" name="low_stock_threshold" class="form-control" value="10">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Cost / Unit</label>
                            <input type="text" id="add_latest_cost_preview" class="form-control" value="0.0000" readonly>
                        </div>

                        <div class="col-md-12 d-grid">
                            <button class="btn btn-dark">Save</button>
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
                            <th>Item</th>
                            <th>Unit</th>
                            <th>Stock</th>
                            <th>Low Stock Alert</th>
                            <th>Status</th>
                            <th>Package Qty</th>
                            <th>Package Cost</th>
                            <th>Cost / Unit</th>
                            <th width="170">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groceries as $grocery): ?>
                            <?php
                                $currentStock = (float)($grocery['current_stock'] ?? 0);
                                $threshold = (float)($grocery['low_stock_threshold'] ?? 0);

                                if ($currentStock <= 0) {
                                    $statusLabel = 'Out of Stock';
                                    $statusClass = 'danger';
                                } elseif ($currentStock <= $threshold) {
                                    $statusLabel = 'Low Stock';
                                    $statusClass = 'warning';
                                } else {
                                    $statusLabel = 'Normal';
                                    $statusClass = 'success';
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($grocery['grocery_name']); ?></td>
                                <td><?= htmlspecialchars($grocery['unit']); ?></td>
                                <td><?= number_format($currentStock, 2); ?></td>
                                <td><?= number_format($threshold, 2); ?></td>
                                <td><span class="badge text-bg-<?= $statusClass; ?>"><?= $statusLabel; ?></span></td>
                                <td><?= number_format((float)($grocery['package_quantity'] ?? 0), 2); ?></td>
                                <td>₱<?= number_format((float)($grocery['package_cost'] ?? 0), 2); ?></td>
                                <td>₱<?= number_format((float)$grocery['latest_cost'], 4); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editGroceryModal<?= (int)$grocery['id']; ?>">
                                            Edit
                                        </button>
                                        <a class="btn btn-danger btn-sm" onclick="return confirm('Delete this grocery item?')" href="<?= $config['base_url']; ?>/groceries/delete/<?= (int)$grocery['id']; ?>">
                                            Del
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($groceries)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No grocery items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php foreach ($groceries as $grocery): ?>
    <div class="modal fade" id="editGroceryModal<?= (int)$grocery['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0">
                <form method="POST" action="<?= $config['base_url']; ?>/groceries/update/<?= (int)$grocery['id']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Grocery Item</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Grocery Name</label>
                                <input type="text" name="grocery_name" class="form-control" value="<?= htmlspecialchars($grocery['grocery_name']); ?>" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Unit</label>
                                <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($grocery['unit']); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Current Stock</label>
                                <input type="number" step="0.01" name="current_stock" class="form-control" value="<?= htmlspecialchars($grocery['current_stock']); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Low Stock Alert</label>
                                <input type="number" step="0.01" min="0" name="low_stock_threshold" class="form-control" value="<?= htmlspecialchars($grocery['low_stock_threshold'] ?? 10); ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Package Quantity</label>
                                <input type="number" step="0.01" min="0.01" name="package_quantity" class="form-control edit-package-quantity" value="<?= htmlspecialchars($grocery['package_quantity'] ?? 1); ?>" required oninput="computeModalUnitCost(this)">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Package Cost</label>
                                <input type="number" step="0.01" min="0" name="package_cost" class="form-control edit-package-cost" value="<?= htmlspecialchars($grocery['package_cost'] ?? 0); ?>" required oninput="computeModalUnitCost(this)">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cost / Unit</label>
                                <input type="text" class="form-control edit-latest-cost-preview" value="<?= number_format((float)$grocery['latest_cost'], 4, '.', ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
function computeAddGroceryValues() {
    const packageCount = parseFloat(document.getElementById('add_package_count').value) || 0;
    const packageQty = parseFloat(document.getElementById('add_package_quantity').value) || 0;
    const packageCost = parseFloat(document.getElementById('add_package_cost').value) || 0;

    const currentStock = packageCount * packageQty;
    const latestCost = packageQty > 0 ? packageCost / packageQty : 0;

    document.getElementById('add_current_stock_preview').value = currentStock.toFixed(2);
    document.getElementById('add_latest_cost_preview').value = latestCost.toFixed(4);
}

function computeModalUnitCost(input) {
    const modal = input.closest('.modal');
    const qtyInput = modal.querySelector('.edit-package-quantity');
    const costInput = modal.querySelector('.edit-package-cost');
    const previewInput = modal.querySelector('.edit-latest-cost-preview');

    const qty = parseFloat(qtyInput.value) || 0;
    const cost = parseFloat(costInput.value) || 0;
    const result = qty > 0 ? (cost / qty) : 0;

    previewInput.value = result.toFixed(4);
}

document.addEventListener('DOMContentLoaded', function () {
    computeAddGroceryValues();

    document.querySelectorAll('.modal').forEach(function(modal) {
        const qtyInput = modal.querySelector('.edit-package-quantity');
        const costInput = modal.querySelector('.edit-package-cost');
        const previewInput = modal.querySelector('.edit-latest-cost-preview');

        if (qtyInput && costInput && previewInput) {
            const qty = parseFloat(qtyInput.value) || 0;
            const cost = parseFloat(costInput.value) || 0;
            previewInput.value = (qty > 0 ? cost / qty : 0).toFixed(4);
        }
    });
});
</script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>