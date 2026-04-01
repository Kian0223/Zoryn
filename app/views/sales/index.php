<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">Sales / POS</h2></div>
        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Create Sale</h5>
                <form method="POST" action="<?= $config['base_url']; ?>/sales/store">
                    <div id="sale-items-wrapper">
                        <div class="row g-2 sale-row mb-2">
                            <div class="col-md-2">
                                <select name="item_type[]" class="form-select item-type" onchange="toggleSaleRow(this)">
                                    <option value="viand">Viand</option>
                                    <option value="product">Product</option>
                                </select>
                            </div>
                            <div class="col-md-4 viand-col">
                                <select name="viand_id[]" class="form-select">
                                    <option value="">-- Select Viand --</option>
                                    <?php foreach ($viands as $viand): ?>
                                        <option value="<?= $viand['id']; ?>" data-price="<?= htmlspecialchars($viand['selling_price']); ?>"><?= htmlspecialchars($viand['viand_name']); ?> (₱<?= number_format((float)$viand['selling_price'], 2); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 product-col d-none">
                                <select name="product_id[]" class="form-select">
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id']; ?>" data-price="<?= htmlspecialchars($product['selling_price']); ?>"><?= htmlspecialchars($product['product_name']); ?> (Stock: <?= number_format((float)$product['current_stock'],2); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2"><input type="number" step="0.01" min="1" name="quantity[]" class="form-control" placeholder="Qty" value="1"></div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" name="unit_price[]" class="form-control" placeholder="Unit Price"></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="addSaleRow()">Add Row</button>
                        <button class="btn btn-dark">Save Sale</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">Today's Sales</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Receipt</th><th>Date</th><th>Items</th><th>Total</th></tr></thead>
                        <tbody>
                            <?php foreach ($recent_sales as $sale): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sale['receipt_no']); ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($sale['sale_date']))); ?></td>
                                    <td>
                                        <?php foreach (($sale['items'] ?? []) as $item): ?>
                                            <div><?= htmlspecialchars($item['item_name'] ?? '-'); ?> x <?= number_format((float)$item['quantity'], 2); ?></div>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>₱<?= number_format((float)$sale['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_sales)): ?>
                                <tr><td colspan="4" class="text-center text-muted">No sales recorded today.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<script>
function toggleSaleRow(selectEl) {
    const row = selectEl.closest('.sale-row');
    const viandCol = row.querySelector('.viand-col');
    const productCol = row.querySelector('.product-col');
    if (selectEl.value === 'product') {
        viandCol.classList.add('d-none');
        productCol.classList.remove('d-none');
    } else {
        productCol.classList.add('d-none');
        viandCol.classList.remove('d-none');
    }
}
function addSaleRow() {
    const wrapper = document.getElementById('sale-items-wrapper');
    const first = wrapper.querySelector('.sale-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('input').forEach(el => {
        if (el.name === 'quantity[]') el.value = '1';
        else el.value = '';
    });
    clone.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    clone.querySelector('.product-col').classList.add('d-none');
    clone.querySelector('.viand-col').classList.remove('d-none');
    wrapper.appendChild(clone);
}
document.addEventListener('change', function(e){
    if (e.target.matches('.viand-col select option, .product-col select option')) return;
    const row = e.target.closest('.sale-row');
    if (!row) return;
    if (e.target.name === 'viand_id[]') {
        const price = e.target.options[e.target.selectedIndex]?.dataset?.price || '';
        row.querySelector('input[name="unit_price[]"]').value = price;
    }
    if (e.target.name === 'product_id[]') {
        const price = e.target.options[e.target.selectedIndex]?.dataset?.price || '';
        row.querySelector('input[name="unit_price[]"]').value = price;
    }
});
</script>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
