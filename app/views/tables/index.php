<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Tables</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Tables</div>
                        <h3 class="mb-0"><?= (int)($summary['total_tables'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Available</div>
                        <h3 class="mb-0 text-success"><?= (int)($summary['available_tables'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Occupied</div>
                        <h3 class="mb-0 text-warning"><?= (int)($summary['occupied_tables'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Reserved</div>
                        <h3 class="mb-0 text-info"><?= (int)($summary['reserved_tables'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Add Table</h5>
                <form method="POST" action="<?= $config['base_url']; ?>/tables/store">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Table Name</label>
                            <input type="text" name="table_name" class="form-control" placeholder="e.g. Table 5" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Capacity</label>
                            <input type="number" name="capacity" class="form-control" min="1" value="4" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Area</label>
                            <input type="text" name="area" class="form-control" placeholder="Main Hall">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="available">Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-grid">
                            <label class="form-label opacity-0">Save</label>
                            <button class="btn btn-dark">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($tables as $table): ?>
                <?php
                    $status = $table['status'] ?? 'available';
                    $badgeClass = match ($status) {
                        'available' => 'success',
                        'occupied' => 'warning',
                        'reserved' => 'info',
                        default => 'secondary',
                    };
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($table['table_name']); ?></h5>
                                    <div class="text-muted small"><?= htmlspecialchars($table['area'] ?: 'No area'); ?></div>
                                </div>
                                <span class="badge text-bg-<?= $badgeClass; ?>"><?= strtoupper(htmlspecialchars($status)); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong>Capacity:</strong> <?= (int)$table['capacity']; ?> guests
                            </div>

                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editTableModal<?= (int)$table['id']; ?>">Edit</button>
                            <a class="btn btn-danger btn-sm" onclick="return confirm('Delete this table?')" href="<?= $config['base_url']; ?>/tables/delete/<?= (int)$table['id']; ?>">Del</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($tables)): ?>
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center text-muted">No tables found.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php foreach ($tables as $table): ?>
    <div class="modal fade" id="editTableModal<?= (int)$table['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <form method="POST" action="<?= $config['base_url']; ?>/tables/update/<?= (int)$table['id']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Table</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Table Name</label>
                            <input type="text" name="table_name" class="form-control" value="<?= htmlspecialchars($table['table_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" name="capacity" class="form-control" min="1" value="<?= (int)$table['capacity']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Area</label>
                            <input type="text" name="area" class="form-control" value="<?= htmlspecialchars($table['area'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['available','occupied','reserved','maintenance'] as $status): ?>
                                    <option value="<?= $status; ?>" <?= ($table['status'] === $status) ? 'selected' : ''; ?>>
                                        <?= ucwords(str_replace('_', ' ', $status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
