<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Orders</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Type</th>
                            <th>Table / Customer</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Created</th>
                            <th width="420">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($orders ?? []) as $order): ?>
                            <?php $statusClass = match ($order['status']) {
                                'pending' => 'secondary',
                                'preparing' => 'warning',
                                'ready' => 'success',
                                'served' => 'info',
                                'completed' => 'dark',
                                'cancelled' => 'danger',
                                default => 'secondary',
                            }; ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_no']); ?></td>
                                <td><?= ucwords(str_replace('_', ' ', htmlspecialchars($order['order_type']))); ?></td>
                                <td>
                                    <?php if (!empty($order['table_name'])): ?><div><?= htmlspecialchars($order['table_name']); ?></div><?php endif; ?>
                                    <?php if (!empty($order['customer_name'])): ?><div class="small text-muted"><?= htmlspecialchars($order['customer_name']); ?></div><?php endif; ?>
                                </td>
                                <td><span class="badge text-bg-<?= $statusClass; ?>"><?= strtoupper(htmlspecialchars($order['status'])); ?></span></td>
                                <td>
                                    <?php foreach (($order['items'] ?? []) as $item): ?>
                                        <div><?= htmlspecialchars($item['item_name'] ?? '-'); ?> x <?= number_format((float)$item['quantity'], 2); ?></div>
                                    <?php endforeach; ?>
                                </td>
                                <td>₱<?= number_format((float)$order['total_amount'], 2); ?></td>
                                <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($order['created_at']))); ?></td>
                                <td>
                                    <?php if (empty($order['sale_id']) && !in_array($order['status'], ['cancelled', 'completed'], true)): ?>
                                        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#checkoutModal<?= (int)$order['id']; ?>">Checkout</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($orders)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php $checkoutCustomers = (new Customer())->getAll(); ?>
<?php foreach (($orders ?? []) as $order): ?>
<?php if (empty($order['sale_id']) && !in_array($order['status'], ['cancelled', 'completed'], true)): ?>
<div class="modal fade" id="checkoutModal<?= (int)$order['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <form method="POST" action="<?= $config['base_url']; ?>/orders/checkout/<?= (int)$order['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Checkout <?= htmlspecialchars($order['order_no']); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bill Total</label>
                        <input type="text" class="form-control" value="₱<?= number_format((float)$order['total_amount'], 2); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select">
                            <option value="">-- Walk-in / No Customer --</option>
                            <?php foreach (($checkoutCustomers ?? []) as $customer): ?>
                                <option value="<?= (int)$customer['id']; ?>"><?= htmlspecialchars($customer['full_name']); ?> (<?= number_format((float)$customer['total_points'], 2); ?> pts)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Promo Code</label>
                        <input type="text" name="promo_code" class="form-control" placeholder="Enter promo code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Redeem Points</label>
                        <input type="number" step="0.01" min="0" name="redeem_points" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference No. (optional)</label>
                        <input type="text" name="payment_reference" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Paid Amount</label>
                        <input type="number" step="0.01" min="<?= htmlspecialchars((float)$order['total_amount']); ?>" name="paid_amount" class="form-control" value="<?= htmlspecialchars((float)$order['total_amount']); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-dark">Confirm Checkout</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
