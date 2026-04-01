<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<main class="content-area flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Supplier Purchase Orders</h2>
    </div>

    <?php require APP_PATH . '/views/partials/alerts.php'; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="po-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Total POs</div><h3 class="mb-0"><?= (int)($summary['total_pos'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="po-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Issued</div><h3 class="mb-0"><?= (int)($summary['issued_pos'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="po-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Partial</div><h3 class="mb-0"><?= (int)($summary['partially_received_pos'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="po-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Received</div><h3 class="mb-0"><?= (int)($summary['received_pos'] ?? 0); ?></h3></div></div></div>
    </div>

    <div class="po-card card shadow-sm border-0 mb-4">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Open PO Balances</h5>
            <table class="table table-hover align-middle">
                <thead><tr><th>PO</th><th>Supplier</th><th>Item</th><th>Ordered</th><th>Received</th><th>Balance</th><th>Receive Line</th></tr></thead>
                <tbody>
                    <?php foreach (($open_balances ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['po_no']); ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                        <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                        <td><?= number_format((float)$row['ordered_qty'], 2); ?> <?= htmlspecialchars($row['unit'] ?? ''); ?></td>
                        <td><?= number_format((float)$row['received_qty'], 2); ?> <?= htmlspecialchars($row['unit'] ?? ''); ?></td>
                        <td><strong><?= number_format((float)$row['balance_qty'], 2); ?> <?= htmlspecialchars($row['unit'] ?? ''); ?></strong></td>
                        <td>
                            <form method="POST" action="<?= $config['base_url']; ?>/supplierpurchaseorders/receiveLine/<?= (int)$row['po_item_id']; ?>" class="row g-2">
                                <input type="hidden" name="po_id" value="<?= (int)$row['po_id']; ?>">
                                <input type="hidden" name="supplier_id" value="<?= (int)$row['supplier_id']; ?>">
                                <input type="hidden" name="grocery_id" value="<?= (int)$row['grocery_id']; ?>">
                                <input type="hidden" name="expected_date" value="<?= htmlspecialchars($row['expected_date'] ?? ''); ?>">
                                <div class="col-md-3"><input type="number" step="0.01" min="0.01" max="<?= htmlspecialchars((string)$row['balance_qty']); ?>" name="delivered_qty" class="form-control form-control-sm" placeholder="Qty" required></div>
                                <div class="col-md-4"><input type="date" name="delivery_date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>"></div>
                                <div class="col-md-3"><input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes"></div>
                                <div class="col-md-2 d-grid"><button class="btn btn-success btn-sm">Receive</button></div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($open_balances)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No open PO balances found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="po-card card shadow-sm border-0">
                <div class="card-body table-responsive">
                    <h5 class="mb-3">Supplier Delivery Performance</h5>
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Supplier</th><th>Deliveries</th><th>Avg Lead Days</th><th>On-Time %</th></tr></thead>
                        <tbody>
                            <?php foreach (($supplier_performance ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                                <td><?= (int)$row['deliveries_count']; ?></td>
                                <td><?= number_format((float)$row['avg_lead_days'], 2); ?></td>
                                <td><?= number_format((float)$row['on_time_rate'], 2); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($supplier_performance)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No delivery performance data yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="po-card card shadow-sm border-0">
                <div class="card-body table-responsive">
                    <h5 class="mb-3">Recent Deliveries</h5>
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Date</th><th>Supplier</th><th>PO</th><th>Item</th><th>Qty</th><th>On Time</th></tr></thead>
                        <tbody>
                            <?php foreach (($recent_deliveries ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['delivery_date']); ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                                <td><?= htmlspecialchars($row['po_no']); ?></td>
                                <td><?= htmlspecialchars($row['grocery_name']); ?></td>
                                <td><?= number_format((float)$row['delivered_qty'], 2); ?></td>
                                <td><span class="badge text-bg-<?= !empty($row['on_time_flag']) ? 'success' : 'danger'; ?>"><?= !empty($row['on_time_flag']) ? 'YES' : 'NO'; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_deliveries)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No delivery logs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="po-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Purchase Orders</h5>
            <table class="table table-hover align-middle">
                <thead><tr><th>PO No</th><th>Supplier</th><th>Status</th><th>Subtotal</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach (($pos ?? []) as $po): ?>
                    <tr>
                        <td><?= htmlspecialchars($po['po_no']); ?></td>
                        <td><?= htmlspecialchars($po['supplier_name']); ?></td>
                        <td><span class="badge text-bg-<?= $po['status']==='received'?'success':($po['status']==='issued'?'warning':($po['status']==='partially_received'?'info':'secondary')); ?>"><?= strtoupper(htmlspecialchars($po['status'])); ?></span></td>
                        <td>₱<?= number_format((float)$po['subtotal'], 2); ?></td>
                        <td><a class="btn btn-outline-secondary btn-sm" target="_blank" href="<?= $config['base_url']; ?>/supplierpurchaseorders/print/<?= (int)$po['id']; ?>">Print</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pos)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No supplier purchase orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
