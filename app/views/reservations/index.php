<?php require APP_PATH . '/views/layouts/header.php'; ?>
<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Reservations</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-3"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Today Reservations</div><h3 class="mb-0"><?= (int)($summary['total_today'] ?? 0); ?></h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Pending</div><h3 class="mb-0 text-warning"><?= (int)($summary['pending_today'] ?? 0); ?></h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Confirmed</div><h3 class="mb-0 text-info"><?= (int)($summary['confirmed_today'] ?? 0); ?></h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm border-0 h-100"><div class="card-body"><div class="text-muted small">Seated</div><h3 class="mb-0 text-success"><?= (int)($summary['seated_today'] ?? 0); ?></h3></div></div></div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Reservation Calendar</h5>
                    <form method="GET" class="d-flex gap-2">
                        <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($calendar_month ?? date('Y-m')); ?>">
                        <button class="btn btn-dark">Go</button>
                    </form>
                </div>
                <div class="row g-3">
                    <?php $grouped = []; foreach (($calendar_reservations ?? []) as $r) { $grouped[$r['reservation_date']][] = $r; } ?>
                    <?php foreach ($grouped as $date => $items): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="calendar-day-card card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <h6 class="mb-3"><?= htmlspecialchars(date('M d, Y', strtotime($date))); ?></h6>
                                    <?php foreach ($items as $item): ?>
                                        <div class="mb-2 pb-2 border-bottom">
                                            <div class="fw-semibold"><?= htmlspecialchars($item['customer_name']); ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars(date('h:i A', strtotime($item['reservation_time']))); ?> · <?= (int)$item['pax_count']; ?> pax</div>
                                            <div class="small text-muted"><?= htmlspecialchars($item['table_name'] ?? 'No table'); ?> · <?= strtoupper(htmlspecialchars($item['status'])); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($grouped)): ?>
                        <div class="col-12"><div class="text-center text-muted">No reservations for this month.</div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Reservation No</th>
                            <th>Guest</th>
                            <th>Date & Time</th>
                            <th>Pax</th>
                            <th>Table</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th width="360">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($reservations ?? []) as $reservation): ?>
                            <?php $statusClass = match ($reservation['status']) {
                                'pending' => 'secondary',
                                'confirmed' => 'info',
                                'seated' => 'success',
                                'completed' => 'dark',
                                'cancelled' => 'danger',
                                'no_show' => 'warning',
                                default => 'secondary',
                            }; ?>
                            <tr>
                                <td><?= htmlspecialchars($reservation['reservation_no']); ?></td>
                                <td><div><?= htmlspecialchars($reservation['customer_name']); ?></div><?php if (!empty($reservation['customer_phone'])): ?><div class="small text-muted"><?= htmlspecialchars($reservation['customer_phone']); ?></div><?php endif; ?></td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($reservation['reservation_date']))); ?><br><span class="small text-muted"><?= htmlspecialchars(date('h:i A', strtotime($reservation['reservation_time']))); ?></span></td>
                                <td><?= (int)$reservation['pax_count']; ?></td>
                                <td><?= htmlspecialchars($reservation['table_name'] ?? '-'); ?></td>
                                <td><span class="badge text-bg-<?= $statusClass; ?>"><?= strtoupper(str_replace('_', ' ', htmlspecialchars($reservation['status']))); ?></span></td>
                                <td><?= htmlspecialchars($reservation['notes'] ?? '-'); ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <?php if (in_array($reservation['status'], ['confirmed','seated'], true)): ?>
                                            <a class="btn btn-dark btn-sm" href="<?= $config['base_url']; ?>/orders/index?reservation_id=<?= (int)$reservation['id']; ?>">Create Order</a>
                                        <?php endif; ?>
                                        <a class="btn btn-danger btn-sm" onclick="return confirm('Delete this reservation?')" href="<?= $config['base_url']; ?>/reservations/delete/<?= (int)$reservation['id']; ?>">Del</a>
                                    </div>
                                    <form method="POST" action="<?= $config['base_url']; ?>/reservations/updateStatus/<?= (int)$reservation['id']; ?>" class="d-flex gap-2">
                                        <select name="status" class="form-select form-select-sm">
                                            <?php foreach (['pending','confirmed','seated','completed','cancelled','no_show'] as $status): ?>
                                                <option value="<?= $status; ?>" <?= ($reservation['status'] === $status) ? 'selected' : ''; ?>><?= ucwords(str_replace('_', ' ', $status)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-outline-secondary btn-sm">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reservations)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No reservations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
