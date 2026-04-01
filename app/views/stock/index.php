<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Stock In / Stock Out</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Record Stock Movement</h5>

                <form method="POST" action="<?= $config['base_url']; ?>/stock/store">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Item Type</label>
                            <select name="item_type" id="itemType" class="form-select">
                                <option value="product">Product</option>
                                <option value="grocery">Grocery</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Movement</label>
                            <select name="movement_type" id="movementType" class="form-select">
                                <option value="stock_in">Stock In</option>
                                <option value="stock_out">Stock Out</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Item</label>
                            <select name="item_id" id="itemSelect" class="form-select" required></select>
                        </div>

                        <div class="col-md-2 grocery-stockin-only d-none">
                            <label class="form-label">Package Count</label>
                            <input type="number" step="0.01" min="0.01" name="package_count" id="package_count" class="form-control" value="1" oninput="computeGroceryTotals()">
                            <div class="form-text text-muted">Example: 5 bottles.</div>
                        </div>

                        <div class="col-md-2 grocery-stockin-only d-none">
                            <label class="form-label">Package Quantity</label>
                            <input type="number" step="0.01" min="0.01" name="package_quantity" id="package_quantity" class="form-control" value="0" oninput="computeGroceryTotals()">
                            <div class="form-text text-muted">Example: 500 ml each.</div>
                        </div>

                        <div class="col-md-2 grocery-stockin-only d-none">
                            <label class="form-label">Package Cost</label>
                            <input type="number" step="0.01" min="0" name="package_cost" id="package_cost" class="form-control" value="0" oninput="computeGroceryTotals()">
                            <div class="form-text text-muted">Cost of one package.</div>
                        </div>

                        <div class="col-md-2 grocery-stockin-only d-none">
                            <label class="form-label">Qty (Auto)</label>
                            <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" class="form-control" value="0" readonly>
                            <div class="form-text text-muted">package count × package qty</div>
                        </div>

                        <div class="col-md-2 grocery-stockin-only d-none">
                            <label class="form-label">Cost / Unit</label>
                            <input type="text" id="latest_cost_preview" class="form-control" value="0.0000" readonly>
                        </div>

                        <div class="col-md-2 product-or-stockout-only">
                            <label class="form-label">Qty</label>
                            <input type="number" step="0.01" min="0.01" name="quantity_manual" id="quantity_manual" class="form-control" value="0">
                            <div class="form-text text-muted">For products or stock out.</div>
                        </div>

                        <div class="col-md-2 product-or-stockout-only">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" step="0.01" min="0" name="unit_cost" id="unit_cost" class="form-control" value="0">
                            <div class="form-text text-muted">Optional log value.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional note">
                        </div>

                        <div class="col-md-12 d-grid">
                            <button class="btn btn-dark">Save Movement</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item Type</th>
                            <th>Item</th>
                            <th>Movement</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Package Count</th>
                            <th>Package Qty</th>
                            <th>Package Cost</th>
                            <th>Cost / Unit</th>
                            <th>Remarks</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $movement): ?>
                            <tr>
                                <td><?= htmlspecialchars($movement['movement_date']); ?></td>
                                <td><?= ucfirst(htmlspecialchars($movement['item_type'])); ?></td>
                                <td><?= htmlspecialchars($movement['item_name'] ?? 'Deleted Item'); ?></td>
                                <td>
                                    <span class="badge <?= $movement['movement_type'] === 'stock_in' ? 'text-bg-success' : 'text-bg-danger'; ?>">
                                        <?= strtoupper(str_replace('_', ' ', $movement['movement_type'])); ?>
                                    </span>
                                </td>
                                <td><?= number_format((float)$movement['quantity'], 2); ?></td>
                                <td><?= htmlspecialchars($movement['unit_name'] ?? '-'); ?></td>
                                <td><?= number_format((float)($movement['package_count'] ?? 0), 2); ?></td>
                                <td><?= number_format((float)($movement['package_quantity'] ?? 0), 2); ?></td>
                                <td>₱<?= number_format((float)($movement['package_cost'] ?? 0), 2); ?></td>
                                <td>₱<?= number_format((float)$movement['unit_cost'], 4); ?></td>
                                <td><?= htmlspecialchars($movement['remarks'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($movement['full_name'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($movements)): ?>
                            <tr>
                                <td colspan="12" class="text-center text-muted">No stock movements found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
const products = <?= json_encode(array_map(
    fn($item) => [
        'id' => $item['id'],
        'name' => $item['product_name'],
        'unit' => $item['unit'] ?? ''
    ],
    $products
)); ?>;

const groceries = <?= json_encode(array_map(
    fn($item) => [
        'id' => $item['id'],
        'name' => $item['grocery_name'],
        'unit' => $item['unit'] ?? '',
        'package_quantity' => $item['package_quantity'] ?? 0,
        'package_cost' => $item['package_cost'] ?? 0,
        'latest_cost' => $item['latest_cost'] ?? 0
    ],
    $groceries
)); ?>;

const itemType = document.getElementById('itemType');
const movementType = document.getElementById('movementType');
const itemSelect = document.getElementById('itemSelect');

const packageCountInput = document.getElementById('package_count');
const packageQuantityInput = document.getElementById('package_quantity');
const packageCostInput = document.getElementById('package_cost');
const quantityInput = document.getElementById('quantity');
const latestCostPreview = document.getElementById('latest_cost_preview');

const quantityManualInput = document.getElementById('quantity_manual');
const unitCostInput = document.getElementById('unit_cost');

function loadItems() {
    const list = itemType.value === 'grocery' ? groceries : products;
    itemSelect.innerHTML = '';

    list.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.name + (item.unit ? ` (${item.unit})` : '');
        option.dataset.packageQuantity = item.package_quantity ?? 0;
        option.dataset.packageCost = item.package_cost ?? 0;
        option.dataset.latestCost = item.latest_cost ?? 0;
        itemSelect.appendChild(option);
    });

    populateGroceryDefaults();
}

function computeGroceryTotals() {
    const packageCount = parseFloat(packageCountInput.value) || 0;
    const packageQty = parseFloat(packageQuantityInput.value) || 0;
    const packageCost = parseFloat(packageCostInput.value) || 0;

    const totalQty = packageCount * packageQty;
    const latestCost = packageQty > 0 ? packageCost / packageQty : 0;

    quantityInput.value = totalQty.toFixed(2);
    latestCostPreview.value = latestCost.toFixed(4);
    unitCostInput.value = latestCost.toFixed(4);
}

function populateGroceryDefaults() {
    if (itemType.value !== 'grocery') return;

    const selected = itemSelect.options[itemSelect.selectedIndex];
    if (!selected) {
        packageCountInput.value = 1;
        packageQuantityInput.value = 0;
        packageCostInput.value = 0;
        computeGroceryTotals();
        return;
    }

    if (movementType.value === 'stock_in') {
        packageCountInput.value = 1;
        packageQuantityInput.value = selected.dataset.packageQuantity || 0;
        packageCostInput.value = selected.dataset.packageCost || 0;
        computeGroceryTotals();
    }
}

function toggleFields() {
    const groceryStockIn = itemType.value === 'grocery' && movementType.value === 'stock_in';

    document.querySelectorAll('.grocery-stockin-only').forEach(el => {
        el.classList.toggle('d-none', !groceryStockIn);
    });

    document.querySelectorAll('.product-or-stockout-only').forEach(el => {
        el.classList.toggle('d-none', groceryStockIn);
    });

    if (groceryStockIn) {
        quantityManualInput.disabled = true;
        unitCostInput.readOnly = true;
        computeGroceryTotals();
    } else {
        quantityManualInput.disabled = false;
        unitCostInput.readOnly = false;
        quantityInput.value = quantityManualInput.value || 0;
    }
}

itemType.addEventListener('change', () => {
    loadItems();
    toggleFields();
});

movementType.addEventListener('change', () => {
    toggleFields();
    populateGroceryDefaults();
});

itemSelect.addEventListener('change', populateGroceryDefaults);

quantityManualInput.addEventListener('input', () => {
    if (!(itemType.value === 'grocery' && movementType.value === 'stock_in')) {
        quantityInput.value = quantityManualInput.value || 0;
    }
});

loadItems();
toggleFields();
computeGroceryTotals();
</script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>