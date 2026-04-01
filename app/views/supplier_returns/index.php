<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Supplier Returns & Credits</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="return-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Total Returns</div><h3 class="mb-0"><?= (int)($summary['total_returns'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="return-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Pending Returns</div><h3 class="mb-0"><?= (int)($summary['pending_returns'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="return-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Open Credit Memos</div><h3 class="mb-0"><?= (int)($credit_summary['open_memos'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="return-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Open Credit Balance</div><h3 class="mb-0">₱<?= number_format((float)($credit_summary['total_open_balance'] ?? 0), 2); ?></h3></div></div></div>
    </div>

    <div class="return-card card shadow-sm border-0 mb-4">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Create Supplier Return</h5>
            <table class="table table-hover align-middle">
                <thead><tr><th>PO</th><th>Supplier</th><th>Item</th><th>Balance</th><th>Return</th></tr></thead>
                <tbody>
                    <?php foreach (($open_balances ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['po_no']); ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                        <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                        <td><?= number_format((float)$row['balance_qty'], 2); ?> <?= htmlspecialchars($row['unit'] ?? ''); ?></td>
                        <td>
                            <form method="POST" action="<?= $config['base_url']; ?>/supplierreturns/store" class="row g-2">
                                <input type="hidden" name="po_id" value="<?= (int)$row['po_id']; ?>">
                                <input type="hidden" name="po_item_id" value="<?= (int)$row['po_item_id']; ?>">
                                <input type="hidden" name="supplier_id" value="<?= (int)$row['supplier_id']; ?>">
                                <input type="hidden" name="grocery_id" value="<?= (int)$row['grocery_id']; ?>">
                                <div class="col-md-2"><input type="date" name="return_date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>"></div>
                                <div class="col-md-2">
                                    <select name="return_type" class="form-select form-select-sm">
                                        <option value="damaged">Damaged</option>
                                        <option value="short_shipment">Short Shipment</option>
                                        <option value="wrong_item">Wrong Item</option>
                                        <option value="over_delivery">Over Delivery</option>
                                        <option value="quality_issue">Quality Issue</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2"><input type="number" step="0.01" min="0.01" name="quantity" class="form-control form-control-sm" placeholder="Qty" required></div>
                                <div class="col-md-2"><input type="number" step="0.01" min="0" name="unit_cost" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$row['unit_cost']); ?>" placeholder="Cost"></div>
                                <div class="col-md-3"><input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes"></div>
                                <div class="col-md-1 d-grid"><button class="btn btn-danger btn-sm">Save</button></div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($open_balances)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No open PO balances available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="return-card card shadow-sm border-0 mb-4">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Supplier Returns</h5>
            <table class="table table-hover align-middle">
                <thead><tr><th>Return No</th><th>Supplier</th><th>Item</th><th>Type</th><th>Qty</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach (($returns ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['return_no']); ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                        <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                        <td><?= strtoupper(str_replace('_', ' ', htmlspecialchars($row['return_type']))); ?></td>
                        <td><?= number_format((float)$row['quantity'], 2); ?> <?= htmlspecialchars($row['unit'] ?? ''); ?></td>
                        <td>₱<?= number_format((float)$row['line_total'], 2); ?></td>
                        <td><span class="badge text-bg-<?= $row['status']==='credited'?'success':($row['status']==='approved'?'warning':'secondary'); ?>"><?= strtoupper(htmlspecialchars($row['status'])); ?></span></td>
                        <td>
                            <?php if (($row['status'] ?? '') === 'pending'): ?>
                                <a class="btn btn-warning btn-sm" href="<?= $config['base_url']; ?>/supplierreturns/approve/<?= (int)$row['id']; ?>">Approve</a>
                            <?php endif; ?>
                            <?php if (in_array(($row['status'] ?? ''), ['approved','pending'], true)): ?>
                                <a class="btn btn-success btn-sm" href="<?= $config['base_url']; ?>/supplierreturns/credit/<?= (int)$row['id']; ?>">Create Memo</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($returns)): ?>
                    <tr><td colspan="8" class="text-center text-muted">No supplier returns found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="return-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Supplier Credit Memos</h5>
            <table class="table table-hover align-middle">
                <thead><tr><th>Memo No</th><th>Supplier</th><th>Source Return</th><th>Amount</th><th>Balance</th><th>Status</th><th>Apply</th></tr></thead>
                <tbody>
                    <?php foreach (($credit_memos ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['memo_no']); ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                        <td><?= htmlspecialchars($row['return_no'] ?? '-'); ?></td>
                        <td>₱<?= number_format((float)$row['amount'], 2); ?></td>
                        <td>₱<?= number_format((float)$row['balance_amount'], 2); ?></td>
                        <td><span class="badge text-bg-<?= in_array(($row['status'] ?? ''), ['open','partial'], true) ? 'warning' : 'success'; ?>"><?= strtoupper(htmlspecialchars($row['status'])); ?></span></td>
                        <td>
                            <?php if (in_array(($row['status'] ?? ''), ['open','partial'], true)): ?>
                            <form method="POST" action="<?= $config['base_url']; ?>/supplierreturns/applyCredit" class="row g-2">
                                <input type="hidden" name="memo_id" value="<?= (int)$row['id']; ?>">
                                <div class="col-md-5"><input type="number" name="purchase_id" class="form-control form-control-sm" placeholder="Purchase ID" required></div>
                                <div class="col-md-4"><input type="number" step="0.01" min="0.01" max="<?= htmlspecialchars((string)$row['balance_amount']); ?>" name="apply_amount" class="form-control form-control-sm" placeholder="Amount" required></div>
                                <div class="col-md-3 d-grid"><button class="btn btn-dark btn-sm">Apply</button></div>
                            </form>
                            <?php else: ?>
                            <span class="text-muted small">Closed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($credit_memos)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No credit memos found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
