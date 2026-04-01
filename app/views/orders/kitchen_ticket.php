<?php $config = require CONFIG_PATH . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Ticket | <?= htmlspecialchars($config['app_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $config['base_url']; ?>/public/assets/css/style.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .print-card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
        body { padding: 24px; }
        .print-card { max-width: 700px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="print-card card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="text-muted small">Kitchen Ticket</div>
                    <h3 class="mb-1"><?= htmlspecialchars($order['order_no']); ?></h3>
                    <div class="small text-muted"><?= htmlspecialchars(date('M d, Y h:i A', strtotime($order['created_at']))); ?></div>
                </div>
                <button class="btn btn-dark no-print" onclick="window.print()">Print</button>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>Order Type:</strong> <?= ucwords(str_replace('_', ' ', htmlspecialchars($order['order_type']))); ?></div>
                <div class="col-md-4"><strong>Table:</strong> <?= htmlspecialchars($order['table_name'] ?? '-'); ?></div>
                <div class="col-md-4"><strong>Status:</strong> <?= strtoupper(htmlspecialchars($order['status'])); ?></div>
            </div>

            <?php if (!empty($order['notes'])): ?>
                <div class="mb-3"><strong>Order Notes:</strong> <?= htmlspecialchars($order['notes']); ?></div>
            <?php endif; ?>

            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th width="120">Qty</th>
                        <th width="180">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($order['items'] ?? []) as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name'] ?? '-'); ?></td>
                            <td><?= number_format((float)$item['quantity'], 2); ?></td>
                            <td><?= htmlspecialchars($item['notes'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($order['items'])): ?>
                        <tr><td colspan="3" class="text-center text-muted">No items found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
