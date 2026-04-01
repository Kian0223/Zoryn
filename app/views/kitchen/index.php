<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Kitchen Display</h2>
            <a href="<?= $config['base_url']; ?>/kitchen/index" class="btn btn-dark">Refresh</a>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-4"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Pending</div><h3 class="mb-0 text-secondary"><?= (int)($counts['pending_orders'] ?? 0); ?></h3></div></div></div>
            <div class="col-md-4"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Preparing</div><h3 class="mb-0 text-warning"><?= (int)($counts['preparing_orders'] ?? 0); ?></h3></div></div></div>
            <div class="col-md-4"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Ready</div><h3 class="mb-0 text-success"><?= (int)($counts['ready_orders'] ?? 0); ?></h3></div></div></div>
        </div>

        <div class="row g-4">
            <?php foreach ($orders as $order): ?>
                <?php $statusClass = match ($order['status']) {
                    'pending' => 'secondary',
                    'preparing' => 'warning',
                    'ready' => 'success',
                    default => 'dark',
                }; ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="text-muted small"><?= htmlspecialchars($order['order_no']); ?></div>
                                    <h5 class="mb-1"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $order['order_type']))); ?></h5>
                                    <div class="small text-muted"><?= !empty($order['table_name']) ? htmlspecialchars($order['table_name']) : htmlspecialchars($order['customer_name'] ?? 'Walk-in'); ?></div>
                                </div>
                                <span class="badge text-bg-<?= $statusClass; ?>"><?= strtoupper(htmlspecialchars($order['status'])); ?></span>
                            </div>

                            <div class="mb-3">
                                <?php foreach (($order['items'] ?? []) as $item): ?>
                                    <div><?= htmlspecialchars($item['item_name'] ?? '-'); ?> × <?= number_format((float)$item['quantity'], 2); ?></div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary btn-sm" href="<?= $config['base_url']; ?>/orders/kitchenTicket/<?= (int)$order['id']; ?>" target="_blank">Print Ticket</a>
                                <form method="POST" action="<?= $config['base_url']; ?>/orders/updateStatus/<?= (int)$order['id']; ?>" class="d-flex gap-2 ms-auto">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (['pending','preparing','ready','served'] as $status): ?>
                                            <option value="<?= $status; ?>" <?= ($order['status'] === $status) ? 'selected' : ''; ?>>
                                                <?= ucwords(str_replace('_', ' ', $status)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-primary btn-sm">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($orders)): ?>
                <div class="col-12"><div class="card shadow-sm border-0"><div class="card-body text-center text-muted">No active kitchen orders.</div></div></div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
