<?php require APP_PATH . '/views/layouts/header.php'; ?>

<style>
    /* Zoryn black & gold theme fix for viands page */
    .viands-page .card {
        background: #111111;
        color: #f5f5f5;
        border: 1px solid rgba(212, 175, 55, 0.18);
        border-radius: 18px;
    }

    .viands-page h2,
    .viands-page h5,
    .viands-page h6,
    .viands-page th,
    .viands-page .form-label {
        color: #f0c14b;
    }

    .viands-page .text-muted,
    .viands-page .small {
        color: #bfbfbf !important;
    }

    .viands-page .form-control,
    .viands-page .form-select {
        background-color: #1a1a1a !important;
        color: #f5f5f5 !important;
        border: 1px solid rgba(212, 175, 55, 0.28) !important;
        border-radius: 14px;
        min-height: 48px;
    }

    .viands-page .form-control::placeholder {
        color: #9f9f9f !important;
    }

    .viands-page .form-control:focus,
    .viands-page .form-select:focus {
        background-color: #1a1a1a !important;
        color: #ffffff !important;
        border-color: #d4af37 !important;
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.18) !important;
    }

    /* Main fix for dropdown list items */
    .viands-page select.form-select,
    .viands-page select.grocery-select {
        background-color: #1a1a1a !important;
        color: #f5f5f5 !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    .viands-page select.form-select option,
    .viands-page select.grocery-select option {
        background-color: #1a1a1a !important;
        color: #f5f5f5 !important;
    }

    .viands-page select.form-select option[value=""],
    .viands-page select.grocery-select option[value=""] {
        color: #b9a26a !important;
    }

    .viands-page .table {
        color: #f5f5f5;
        margin-bottom: 0;
    }

    .viands-page .table thead th {
        border-bottom: 1px solid rgba(212, 175, 55, 0.25);
    }

    .viands-page .table td,
    .viands-page .table th {
        background: transparent !important;
        border-color: rgba(212, 175, 55, 0.1);
        vertical-align: middle;
    }

    .viands-page .btn-dark {
        background: #d4af37;
        border-color: #d4af37;
        color: #111 !important;
        font-weight: 600;
    }

    .viands-page .btn-dark:hover {
        background: #e2bf56;
        border-color: #e2bf56;
        color: #111 !important;
    }

    .viands-page .btn-secondary {
        background: #2a2a2a;
        border-color: #3a3a3a;
        color: #f5f5f5;
    }

    .viands-page .btn-outline-danger {
        border-radius: 14px;
    }

    .viands-page .content-area {
        background: #0b0b0b;
        min-height: 100vh;
    }
</style>

<div class="d-flex viands-page">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Viands</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Add Viand Recipe</h5>

                <form method="POST" action="<?= $config['base_url']; ?>/viands/store">
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Viand Name</label>
                            <input type="text" name="viand_name" class="form-control" placeholder="e.g. Garlic Chicken" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Selling Price</label>
                            <input type="number" step="0.01" min="0" name="selling_price" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Description / Notes</label>
                            <input type="text" name="description" class="form-control" placeholder="Optional recipe note">
                        </div>
                    </div>

                    <h6 class="mb-3">Ingredients from Grocery Inventory</h6>
                    <p class="text-muted small mb-3">
                        Select grocery items like chicken, garlic, soy sauce, then enter how much of each item is needed for one serving or one recipe batch.
                    </p>

                    <div id="ingredient-wrapper">
                        <div class="row g-2 mb-2 ingredient-row">
                            <div class="col-md-5">
                                <select name="grocery_id[]" class="form-select grocery-select" onchange="updateIngredientMeta(this)" required>
                                    <option value="">-- Select Grocery Item --</option>
                                    <?php foreach (($groceries ?? []) as $grocery): ?>
                                        <option value="<?= (int)$grocery['id']; ?>"
                                                data-unit="<?= htmlspecialchars($grocery['unit']); ?>"
                                                data-cost="<?= htmlspecialchars($grocery['latest_cost']); ?>">
                                            <?= htmlspecialchars($grocery['grocery_name']); ?>
                                            (<?= htmlspecialchars($grocery['unit']); ?> | ₱<?= number_format((float)$grocery['latest_cost'], 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" min="0.01" name="quantity_needed[]" class="form-control" placeholder="Quantity needed" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control ingredient-unit" placeholder="Unit" readonly>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="button" class="btn btn-outline-danger" onclick="removeIngredientRow(this)">Remove</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-secondary" onclick="addIngredientRow()">+ Add Ingredient</button>
                        <button type="submit" class="btn btn-dark">Save Viand</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Viand</th>
                            <th>Selling Price</th>
                            <th>Total Ingredient Cost</th>
                            <th>Profit Estimate</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($viands ?? []) as $viand): ?>
                            <?php
                                $sellingPrice = (float)($viand['selling_price'] ?? 0);
                                $ingredientCost = (float)($viand['total_ingredient_cost'] ?? 0);
                                $profitEstimate = $sellingPrice - $ingredientCost;
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($viand['viand_name'] ?? ''); ?></div>
                                    <?php if (!empty($viand['description'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($viand['description']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>₱<?= number_format($sellingPrice, 2); ?></td>
                                <td>₱<?= number_format($ingredientCost, 2); ?></td>
                                <td class="<?= $profitEstimate < 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold'; ?>">
                                    ₱<?= number_format($profitEstimate, 2); ?>
                                </td>
                                <td class="d-flex gap-1">
                                    <a class="btn btn-sm btn-dark" href="<?= $config['base_url']; ?>/viands/show/<?= (int)$viand['id']; ?>">View</a>
                                    <a class="btn btn-sm btn-danger" onclick="return confirm('Delete this viand?')" href="<?= $config['base_url']; ?>/viands/delete/<?= (int)$viand['id']; ?>">Del</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($viands)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No viands found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
function updateIngredientMeta(selectElement) {
    const selected = selectElement.options[selectElement.selectedIndex];
    const row = selectElement.closest('.ingredient-row');
    row.querySelector('.ingredient-unit').value = selected.dataset.unit || '';
}

function addIngredientRow() {
    const wrapper = document.getElementById('ingredient-wrapper');
    const firstRow = wrapper.querySelector('.ingredient-row');
    const clone = firstRow.cloneNode(true);
    clone.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    clone.querySelectorAll('input').forEach(el => el.value = '');
    wrapper.appendChild(clone);
}

function removeIngredientRow(button) {
    const rows = document.querySelectorAll('.ingredient-row');
    if (rows.length > 1) {
        button.closest('.ingredient-row').remove();
    } else {
        const row = button.closest('.ingredient-row');
        row.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
        row.querySelectorAll('input').forEach(el => el.value = '');
    }
}
</script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>