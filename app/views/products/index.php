<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Products</h2>
        </div>
        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Add Product</h5>
                <form method="POST" action="<?= $config['base_url']; ?>/products/store">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="product_name" class="form-control" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control" value="pcs">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Sell Price</label>
                            <input type="number" step="0.01" name="selling_price" class="form-control" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Cost Price</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control" value="0">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Stock</label>
                            <input type="number" step="0.01" name="current_stock" class="form-control" value="0">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Min Stock</label>
                            <input type="number" step="0.01" name="low_stock_threshold" class="form-control" value="5">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier_name" class="form-control">
                        </div>
                        <div class="col-md-1 d-grid">
                            <label class="form-label opacity-0">Save</label>
                            <button class="btn btn-dark">Save</button>
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
                            <th>Product</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Unit</th>
                            <th>Sell</th>
                            <th>Cost</th>
                            <th>Stock</th>
                            <th>Min</th>
                            <th>Supplier</th>
                            <th width="520">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_name']); ?></td>
                                <td><?= htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($product['sku'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($product['unit'] ?? '-'); ?></td>
                                <td>₱<?= number_format((float)$product['selling_price'], 2); ?></td>
                                <td>₱<?= number_format((float)($product['cost_price'] ?? 0), 2); ?></td>
                                <td><?= number_format((float)$product['current_stock'], 2); ?></td>
                                <td><?= number_format((float)($product['low_stock_threshold'] ?? 5), 2); ?></td>
                                <td><?= htmlspecialchars($product['supplier_name'] ?? '-'); ?></td>
                                <td>
                                    <form class="row g-2" method="POST" action="<?= $config['base_url']; ?>/products/update/<?= $product['id']; ?>">
                                        <div class="col-md-2">
                                            <select name="category_id" class="form-select form-select-sm">
                                                <option value="">Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['id']; ?>" <?= (int)$product['category_id'] === (int)$category['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($category['category_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2"><input type="text" name="product_name" class="form-control form-control-sm" value="<?= htmlspecialchars($product['product_name']); ?>"></div>
                                        <div class="col-md-1"><input type="text" name="sku" class="form-control form-control-sm" value="<?= htmlspecialchars($product['sku']); ?>"></div>
                                        <div class="col-md-1"><input type="text" name="unit" class="form-control form-control-sm" value="<?= htmlspecialchars($product['unit']); ?>"></div>
                                        <div class="col-md-1"><input type="number" step="0.01" name="selling_price" class="form-control form-control-sm" value="<?= htmlspecialchars($product['selling_price']); ?>"></div>
                                        <div class="col-md-1"><input type="number" step="0.01" name="cost_price" class="form-control form-control-sm" value="<?= htmlspecialchars($product['cost_price'] ?? 0); ?>"></div>
                                        <div class="col-md-1"><input type="number" step="0.01" name="current_stock" class="form-control form-control-sm" value="<?= htmlspecialchars($product['current_stock']); ?>"></div>
                                        <div class="col-md-1"><input type="number" step="0.01" name="low_stock_threshold" class="form-control form-control-sm" value="<?= htmlspecialchars($product['low_stock_threshold'] ?? 5); ?>"></div>
                                        <div class="col-md-1"><input type="text" name="supplier_name" class="form-control form-control-sm" value="<?= htmlspecialchars($product['supplier_name'] ?? ''); ?>"></div>
                                        <div class="col-md-2 d-flex gap-1">
                                            <button class="btn btn-primary btn-sm">Save</button>
                                            <a class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')" href="<?= $config['base_url']; ?>/products/delete/<?= $product['id']; ?>">Del</a>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="10" class="text-center text-muted">No products found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
