<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Viand Costing</h2>
            <a href="<?= $config['base_url']; ?>/viands/index" class="btn btn-dark">Back</a>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <?php if (!empty($summary)): ?>
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($summary['viand_name'] ?? ''); ?></h4>
                            <?php if (!empty($summary['description'])): ?>
                                <div class="text-muted"><?= htmlspecialchars($summary['description']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4 mb-2">
                            <strong>Selling Price</strong><br>
                            ₱<?= number_format((float)($summary['selling_price'] ?? 0), 2); ?>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Total Ingredient Cost</strong><br>
                            ₱<?= number_format((float)($summary['total_cost'] ?? 0), 2); ?>
                        </div>
                        <div class="col-md-4 mb-2">
                            <?php $profit = (float)($summary['profit_estimate'] ?? 0); ?>
                            <strong>Profit Estimate</strong><br>
                            <span class="<?= $profit < 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold'; ?>">
                                ₱<?= number_format($profit, 2); ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-muted">No costing summary found.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Grocery Item</th>
                            <th>Qty Needed</th>
                            <th>Unit</th>
                            <th>Latest Cost / Unit</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($ingredients ?? []) as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['grocery_name'] ?? ''); ?></td>
                                <td><?= number_format((float)($item['quantity_needed'] ?? 0), 2); ?></td>
                                <td><?= htmlspecialchars($item['unit'] ?? ''); ?></td>
                                <td>₱<?= number_format((float)($item['latest_cost'] ?? 0), 2); ?></td>
                                <td>₱<?= number_format((float)($item['subtotal'] ?? 0), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($ingredients)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No ingredients found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
