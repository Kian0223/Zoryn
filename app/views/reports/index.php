<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Reports & Analytics</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Sales History</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th width="280">Adjustments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($sales ?? []) as $sale): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sale['receipt_no']); ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($sale['sale_date']))); ?></td>
                                    <td><span class="badge text-bg-<?= ($sale['sale_status'] ?? 'completed') === 'completed' ? 'success' : (($sale['sale_status'] ?? '') === 'voided' ? 'danger' : 'warning'); ?>"><?= strtoupper(htmlspecialchars($sale['sale_status'] ?? 'completed')); ?></span></td>
                                    <td>
                                        <?php foreach (($sale['items'] ?? []) as $item): ?>
                                            <div><?= htmlspecialchars($item['item_name'] ?? '-'); ?> x <?= number_format((float)$item['quantity'], 2); ?></div>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>₱<?= number_format((float)$sale['total_amount'], 2); ?></td>
                                    <td><?= strtoupper(htmlspecialchars($sale['payment_method'] ?? 'cash')); ?></td>
                                    <td>
                                        <?php if (($sale['sale_status'] ?? 'completed') === 'completed'): ?>
                                            <div class="d-flex flex-column gap-2">
                                                <form method="POST" action="<?= $config['base_url']; ?>/salesadjustments/store/<?= (int)$sale['id']; ?>" class="d-flex gap-2">
                                                    <input type="hidden" name="adjustment_type" value="void">
                                                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="Void reason" required>
                                                    <button class="btn btn-danger btn-sm">Void</button>
                                                </form>
                                                <form method="POST" action="<?= $config['base_url']; ?>/salesadjustments/store/<?= (int)$sale['id']; ?>" class="d-flex gap-2">
                                                    <input type="hidden" name="adjustment_type" value="refund">
                                                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="Refund reason" required>
                                                    <input type="number" step="0.01" min="0.01" max="<?= htmlspecialchars((float)$sale['total_amount']); ?>" name="amount" class="form-control form-control-sm" placeholder="Amount" required>
                                                    <button class="btn btn-warning btn-sm">Refund</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="adjustment-chip"><?= strtoupper(htmlspecialchars($sale['sale_status'] ?? '')); ?></span>
                                            <?php if (!empty($sale['void_reason'])): ?>
                                                <div class="small mt-2"><?= htmlspecialchars($sale['void_reason']); ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($sales)): ?>
                                <tr><td colspan="7" class="text-center text-muted">No sales found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
