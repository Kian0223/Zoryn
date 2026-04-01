<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Supplier Payments</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Record Supplier Payment</h5>
            <form method="POST" action="<?= $config['base_url']; ?>/supplierpayments/store">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Select Supplier --</option>
                            <?php foreach (($suppliers ?? []) as $supplier): ?>
                                <option value="<?= (int)$supplier['id']; ?>"><?= htmlspecialchars($supplier['supplier_name']); ?> (Balance: ₱<?= number_format((float)($supplier['total_balance_due'] ?? 0), 2); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Purchase</label>
                        <select name="purchase_id" class="form-select">
                            <option value="">-- Optional Purchase --</option>
                            <?php foreach (($purchases ?? []) as $purchase): ?>
                                <option value="<?= (int)$purchase['id']; ?>"><?= htmlspecialchars($purchase['purchase_no']); ?> - <?= htmlspecialchars($purchase['supplier_name'] ?? '-'); ?> (Bal ₱<?= number_format((float)($purchase['balance_due'] ?? 0), 2); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reference No.</label>
                        <input type="text" name="reference_no" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                    <div class="col-md-3 d-grid">
                        <label class="form-label opacity-0">Save</label>
                        <button class="btn btn-dark">Save Payment</button>
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
                        <th>Supplier</th>
                        <th>Purchase</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($payments ?? []) as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['payment_date']); ?></td>
                            <td><?= htmlspecialchars($payment['supplier_name'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($payment['purchase_no'] ?? '-'); ?></td>
                            <td>₱<?= number_format((float)$payment['amount'], 2); ?></td>
                            <td><?= strtoupper(htmlspecialchars($payment['payment_method'])); ?></td>
                            <td><?= htmlspecialchars($payment['reference_no'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($payment['notes'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No supplier payments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
