<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="container py-4">
    <div class="po-card card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h2 class="mb-1">Purchase Order</h2>
                    <div><strong>PO No:</strong> <?= htmlspecialchars($po['po_no']); ?></div>
                    <div><strong>PO Date:</strong> <?= htmlspecialchars($po['po_date']); ?></div>
                    <div><strong>Status:</strong> <?= strtoupper(htmlspecialchars($po['status'])); ?></div>
                </div>
                <button class="btn btn-dark" onclick="window.print()">Print</button>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h5>Supplier</h5>
                    <div><strong>Name:</strong> <?= htmlspecialchars($po['supplier_name']); ?></div>
                    <div><strong>Contact:</strong> <?= htmlspecialchars($po['contact_person'] ?? '-'); ?></div>
                    <div><strong>Phone:</strong> <?= htmlspecialchars($po['phone'] ?? '-'); ?></div>
                    <div><strong>Email:</strong> <?= htmlspecialchars($po['email'] ?? '-'); ?></div>
                    <div><strong>Address:</strong> <?= htmlspecialchars($po['address'] ?? '-'); ?></div>
                </div>
                <div class="col-md-6">
                    <h5>Reference</h5>
                    <div><strong>Plan No:</strong> <?= htmlspecialchars($po['plan_no'] ?? '-'); ?></div>
                    <div><strong>Expected Date:</strong> <?= htmlspecialchars($po['expected_date'] ?? '-'); ?></div>
                    <div><strong>Notes:</strong> <?= htmlspecialchars($po['notes'] ?? '-'); ?></div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Item</th><th>Qty</th><th>Unit</th><th>Unit Cost</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach (($po['items'] ?? []) as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['grocery_name']); ?></td>
                            <td><?= number_format((float)$item['ordered_qty'], 2); ?></td>
                            <td><?= htmlspecialchars($item['unit'] ?? '-'); ?></td>
                            <td>₱<?= number_format((float)$item['unit_cost'], 2); ?></td>
                            <td>₱<?= number_format((float)$item['line_total'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($po['items'])): ?>
                        <tr><td colspan="5" class="text-center text-muted">No PO items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Subtotal</th>
                            <th>₱<?= number_format((float)$po['subtotal'], 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-5">
                <div class="col-md-6">
                    <div class="border-top pt-2">Prepared By</div>
                </div>
                <div class="col-md-6">
                    <div class="border-top pt-2">Approved By</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
