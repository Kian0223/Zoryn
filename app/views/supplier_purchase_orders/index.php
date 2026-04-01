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
        <div class="col-md-3"><div class="po-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Draft</div><h3 class="mb-0"><?= (int)($summary['draft_pos'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="po-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Issued</div><h3 class="mb-0"><?= (int)($summary['issued_pos'] ?? 0); ?></h3></div></div></div>
        <div class="col-md-3"><div class="po-card card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Received</div><h3 class="mb-0"><?= (int)($summary['received_pos'] ?? 0); ?></h3></div></div></div>
    </div>

    <div class="po-card card shadow-sm border-0 mb-4">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Approved Plans Ready for PO Conversion</h5>
            <table class="table table-hover align-middle">
                <thead><tr><th>Plan No</th><th>Date</th><th>Status</th><th>Notes</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach (($plans ?? []) as $plan): ?>
                        <?php if (($plan['status'] ?? '') === 'approved'): ?>
                        <tr>
                            <td><?= htmlspecialchars($plan['plan_no']); ?></td>
                            <td><?= htmlspecialchars($plan['plan_date']); ?></td>
                            <td><span class="badge text-bg-success">APPROVED</span></td>
                            <td><?= htmlspecialchars($plan['notes'] ?? '-'); ?></td>
                            <td><a class="btn btn-dark btn-sm" href="<?= $config['base_url']; ?>/supplierpurchaseorders/createFromPlan/<?= (int)$plan['id']; ?>">Create PO(s)</a></td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty(array_filter(($plans ?? []), fn($p) => ($p['status'] ?? '') === 'approved'))): ?>
                    <tr><td colspan="5" class="text-center text-muted">No approved plans ready for conversion.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="po-card card shadow-sm border-0">
        <div class="card-body table-responsive">
            <h5 class="mb-3">Supplier Purchase Orders</h5>
            <table class="table table-hover align-middle">
                <thead><tr><th>PO No</th><th>Supplier</th><th>Plan</th><th>Date</th><th>Status</th><th>Subtotal</th><th>Items</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach (($pos ?? []) as $po): ?>
                    <tr>
                        <td><?= htmlspecialchars($po['po_no']); ?></td>
                        <td><?= htmlspecialchars($po['supplier_name']); ?></td>
                        <td><?= htmlspecialchars($po['plan_no'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($po['po_date']); ?></td>
                        <td><span class="badge text-bg-<?= $po['status']==='received'?'success':($po['status']==='issued'?'warning':($po['status']==='partially_received'?'info':'secondary')); ?>"><?= strtoupper(htmlspecialchars($po['status'])); ?></span></td>
                        <td>₱<?= number_format((float)$po['subtotal'], 2); ?></td>
                        <td>
                            <?php foreach (($po['items'] ?? []) as $item): ?>
                                <div><?= htmlspecialchars($item['grocery_name']); ?> · <?= number_format((float)$item['ordered_qty'], 2); ?> @ ₱<?= number_format((float)$item['unit_cost'], 2); ?></div>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php if (($po['status'] ?? '') === 'draft'): ?>
                                <a class="btn btn-warning btn-sm" href="<?= $config['base_url']; ?>/supplierpurchaseorders/issue/<?= (int)$po['id']; ?>">Issue</a>
                            <?php endif; ?>
                            <a class="btn btn-outline-secondary btn-sm" target="_blank" href="<?= $config['base_url']; ?>/supplierpurchaseorders/print/<?= (int)$po['id']; ?>">Print</a>
                            <?php if (in_array(($po['status'] ?? ''), ['issued','partially_received'], true)): ?>
                                <a class="btn btn-success btn-sm" href="<?= $config['base_url']; ?>/supplierpurchaseorders/createReceivingFromPO/<?= (int)$po['id']; ?>">Receive</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pos)): ?>
                    <tr><td colspan="8" class="text-center text-muted">No supplier purchase orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
