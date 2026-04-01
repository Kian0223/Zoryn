<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Grocery Receiving</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Balance Due</div><h3 class="mb-0">₱<?= number_format((float)($summary['total_balance_due'] ?? 0), 2); ?></h3></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Overdue Balance</div><h3 class="mb-0 text-danger">₱<?= number_format((float)($summary['overdue_balance'] ?? 0), 2); ?></h3></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Current Bucket</div><h3 class="mb-0">₱<?= number_format((float)($aging['current_bucket'] ?? 0), 2); ?></h3></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">90+ Days</div><h3 class="mb-0 text-warning">₱<?= number_format((float)($aging['bucket_90_plus'] ?? 0), 2); ?></h3></div></div></div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Create Grocery Purchase</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/grocerypurchases/store">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- Optional Supplier --</option>
                            <?php foreach (($suppliers ?? []) as $supplier): ?>
                                <option value="<?= (int)$supplier['id']; ?>"><?= htmlspecialchars($supplier['supplier_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="<?= date('Y-m-d'); ?>" required oninput="computeDefaultDueDate()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Default Days Due</label>
                        <input type="text" class="form-control" id="default_days_due" value="<?= (int)($default_days_due ?? 0); ?>" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional note">
                    </div>
                </div>

                <div id="purchase-items-wrapper">
                    <div class="row g-2 purchase-row mb-2">
                        <div class="col-md-4">
                            <select name="grocery_id[]" class="form-select">
                                <option value="">-- Select Grocery --</option>
                                <?php foreach (($groceries ?? []) as $grocery): ?>
                                    <option value="<?= (int)$grocery['id']; ?>" data-package-quantity="<?= htmlspecialchars($grocery['package_quantity'] ?? 0); ?>" data-package-cost="<?= htmlspecialchars($grocery['package_cost'] ?? 0); ?>">
                                        <?= htmlspecialchars($grocery['grocery_name']); ?> (<?= htmlspecialchars($grocery['unit'] ?? ''); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="number" step="0.01" min="0.01" name="package_count[]" class="form-control purchase-package-count" value="1" placeholder="Pack Count"></div>
                        <div class="col-md-2"><input type="number" step="0.01" min="0.01" name="package_quantity[]" class="form-control purchase-package-quantity" placeholder="Pack Qty"></div>
                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="package_cost[]" class="form-control purchase-package-cost" placeholder="Pack Cost"></div>
                        <div class="col-md-2"><input type="text" class="form-control purchase-line-total" placeholder="Line Total" readonly></div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary" onclick="addPurchaseRow()">Add Row</button>
                    <button class="btn btn-dark">Save Draft Purchase</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Purchase No</th>
                        <th>Supplier</th>
                        <th>Purchase Date</th>
                        <th>Due Date</th>
                        <th>Due Status</th>
                        <th>Total</th>
                        <th>Balance</th>
                        <th>Payment</th>
                        <th>Expense Posted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($purchases ?? []) as $purchase): ?>
                        <?php
                            $dueClass = 'secondary';
                            if (($purchase['due_status'] ?? '') === 'overdue') $dueClass = 'danger';
                            elseif (($purchase['due_status'] ?? '') === 'due_today') $dueClass = 'warning';
                            elseif (($purchase['due_status'] ?? '') === 'paid') $dueClass = 'success';
                            elseif (($purchase['due_status'] ?? '') === 'upcoming') $dueClass = 'info';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($purchase['purchase_no']); ?></td>
                            <td><?= htmlspecialchars($purchase['supplier_name'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($purchase['purchase_date']); ?></td>
                            <td><?= htmlspecialchars($purchase['due_date'] ?? '-'); ?></td>
                            <td><span class="badge text-bg-<?= $dueClass; ?>"><?= strtoupper(str_replace('_', ' ', htmlspecialchars($purchase['due_status'] ?? ''))); ?></span></td>
                            <td>₱<?= number_format((float)$purchase['total_amount'], 2); ?></td>
                            <td>₱<?= number_format((float)($purchase['balance_due'] ?? 0), 2); ?></td>
                            <td><span class="badge text-bg-<?= ($purchase['payment_status'] === 'paid') ? 'success' : (($purchase['payment_status'] === 'partial') ? 'warning' : 'secondary'); ?>"><?= strtoupper(htmlspecialchars($purchase['payment_status'] ?? 'unpaid')); ?></span></td>
                            <td><span class="badge text-bg-<?= !empty($purchase['expense_posted']) ? 'success' : 'secondary'; ?>"><?= !empty($purchase['expense_posted']) ? 'YES' : 'NO'; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($purchases)): ?>
                        <tr><td colspan="9" class="text-center text-muted">No grocery purchases found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>

<script>
function computePurchaseRow(row) {
    const count = parseFloat(row.querySelector('.purchase-package-count').value) || 0;
    const cost = parseFloat(row.querySelector('.purchase-package-cost').value) || 0;
    row.querySelector('.purchase-line-total').value = (count * cost).toFixed(2);
}

function addPurchaseRow() {
    const wrapper = document.getElementById('purchase-items-wrapper');
    const first = wrapper.querySelector('.purchase-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('input').forEach(el => {
        if (el.classList.contains('purchase-package-count')) el.value = '1';
        else el.value = '';
    });
    clone.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    wrapper.appendChild(clone);
}

function computeDefaultDueDate() {
    const purchaseDateInput = document.getElementById('purchase_date');
    const dueDateInput = document.getElementById('due_date');
    const defaultDays = parseInt(document.getElementById('default_days_due').value || '0', 10);
    if (!purchaseDateInput.value) return;

    const d = new Date(purchaseDateInput.value + 'T00:00:00');
    d.setDate(d.getDate() + defaultDays);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    dueDateInput.value = `${yyyy}-${mm}-${dd}`;
}

document.addEventListener('change', function(e) {
    const row = e.target.closest('.purchase-row');
    if (!row) return;

    if (e.target.name === 'grocery_id[]') {
        const opt = e.target.options[e.target.selectedIndex];
        if (opt) {
            row.querySelector('.purchase-package-quantity').value = opt.dataset.packageQuantity || '';
            row.querySelector('.purchase-package-cost').value = opt.dataset.packageCost || '';
            computePurchaseRow(row);
        }
    }
});

document.addEventListener('input', function(e) {
    const row = e.target.closest('.purchase-row');
    if (!row) return;
    if (e.target.classList.contains('purchase-package-count') || e.target.classList.contains('purchase-package-cost')) {
        computePurchaseRow(row);
    }
});

document.addEventListener('DOMContentLoaded', computeDefaultDueDate);
</script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
