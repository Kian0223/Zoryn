<?php
$config = require CONFIG_PATH . '/config.php';
$currentUrl = $_GET['url'] ?? 'dashboard/index';

$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill', 'url' => '/dashboard/index', 'match' => 'dashboard'],
    ['label' => 'Supplier Returns', 'icon' => 'bi-arrow-return-left', 'url' => '/supplierreturns/index', 'match' => 'supplierreturns'],
    ['label' => 'Supplier POs', 'icon' => 'bi-file-earmark-text', 'url' => '/supplierpurchaseorders/index', 'match' => 'supplierpurchaseorders'],
    ['label' => 'Purchase Planning', 'icon' => 'bi-clipboard2-check', 'url' => '/purchaseplans/index', 'match' => 'purchaseplans'],
    ['label' => 'Inventory Forecast', 'icon' => 'bi-graph-down-arrow', 'url' => '/inventoryforecast/index', 'match' => 'inventoryforecast'],
    ['label' => 'HR Dashboard', 'icon' => 'bi-clipboard-pulse', 'url' => '/hrdashboard/index', 'match' => 'hrdashboard'],
    ['label' => 'Analytics', 'icon' => 'bi-graph-up-arrow', 'url' => '/analytics/index', 'match' => 'analytics'],
    ['label' => 'Performance Reports', 'icon' => 'bi-speedometer2', 'url' => '/reporting/index', 'match' => 'reporting'],
    ['label' => 'Employees', 'icon' => 'bi-person-badge', 'url' => '/employees/index', 'match' => 'employees'],
    ['label' => 'Attendance', 'icon' => 'bi-clock-history', 'url' => '/attendance/index', 'match' => 'attendance'],
    ['label' => 'Leave Requests', 'icon' => 'bi-calendar-x', 'url' => '/leaves/index', 'match' => 'leaves'],
    ['label' => 'Payroll Adjustments', 'icon' => 'bi-sliders', 'url' => '/payrolladjustments/index', 'match' => 'payrolladjustments'],
    ['label' => 'Payroll Summary', 'icon' => 'bi-cash-coin', 'url' => '/payroll/index', 'match' => 'payroll'],
    ['label' => 'Customers', 'icon' => 'bi-people', 'url' => '/customers/index', 'match' => 'customers'],
    ['label' => 'Loyalty Analytics', 'icon' => 'bi-stars', 'url' => '/loyalty/index', 'match' => 'loyalty'],
    ['label' => 'Promo Codes', 'icon' => 'bi-ticket-perforated', 'url' => '/promos/index', 'match' => 'promos'],
    ['label' => 'Birthday Rewards', 'icon' => 'bi-gift', 'url' => '/birthdayrewards/index', 'match' => 'birthdayrewards'],
    ['label' => 'Sales / POS', 'icon' => 'bi-cart-check-fill', 'url' => '/sales/index', 'match' => 'sales'],
    ['label' => 'Orders', 'icon' => 'bi-receipt-cutoff', 'url' => '/orders/index', 'match' => 'orders'],
    ['label' => 'Kitchen Display', 'icon' => 'bi-display-fill', 'url' => '/kitchen/index', 'match' => 'kitchen'],
    ['label' => 'Tables', 'icon' => 'bi-grid-3x3-gap-fill', 'url' => '/tables/index', 'match' => 'tables'],
    ['label' => 'Reservations', 'icon' => 'bi-calendar2-check-fill', 'url' => '/reservations/index', 'match' => 'reservations'],
    ['label' => 'Sales Report', 'icon' => 'bi-bar-chart-line-fill', 'url' => '/reports/index', 'match' => 'reports'],
    ['label' => 'Cashier Shifts', 'icon' => 'bi-cash-stack', 'url' => '/cashiershifts/index', 'match' => 'cashiershifts'],
    ['label' => 'Audit Trail', 'icon' => 'bi-journal-text', 'url' => '/auditlogs/index', 'match' => 'auditlogs'],
    ['label' => 'Suppliers', 'icon' => 'bi-truck', 'url' => '/suppliers/index', 'match' => 'suppliers'],
    ['label' => 'AP Terms', 'icon' => 'bi-calendar-range', 'url' => '/apterms/index', 'match' => 'apterms'],
    ['label' => 'Grocery Receiving', 'icon' => 'bi-box-arrow-in-down', 'url' => '/grocerypurchases/index', 'match' => 'grocerypurchases'],
    ['label' => 'Supplier Payments', 'icon' => 'bi-wallet2', 'url' => '/supplierpayments/index', 'match' => 'supplierpayments'],
    ['label' => 'Inventory Summary', 'icon' => 'bi-clipboard-data', 'url' => '/inventorysummary/index', 'match' => 'inventorysummary'],
    ['label' => 'Products', 'icon' => 'bi-box-seam-fill', 'url' => '/products/index', 'match' => 'products'],
    ['label' => 'Categories', 'icon' => 'bi-tags-fill', 'url' => '/categories/index', 'match' => 'categories'],
    ['label' => 'Stock In / Out', 'icon' => 'bi-arrow-left-right', 'url' => '/stock/index', 'match' => 'stock'],
    ['label' => 'Groceries', 'icon' => 'bi-basket2-fill', 'url' => '/groceries/index', 'match' => 'groceries'],
    ['label' => 'Viands', 'icon' => 'bi-egg-fried', 'url' => '/viands/index', 'match' => 'viands'],
    ['label' => 'Expenses', 'icon' => 'bi-receipt', 'url' => '/expenses/index', 'match' => 'expenses'],
    ['label' => 'Users', 'icon' => 'bi-people-fill', 'url' => '/users/index', 'match' => 'users'],
];
?>
<aside class="zoryn-sidebar">
    <div class="zoryn-sidebar-inner">
        <div class="zoryn-brand-block">
            <div class="zoryn-brand-mark">Z</div>
            <div>
                <div class="zoryn-brand-name">Zoryn Restaurant</div>
                <div class="zoryn-brand-tag">Black &amp; Gold Admin</div>
            </div>
        </div>
        <div class="zoryn-panel-label">Main Menu</div>
        <nav class="nav flex-column zoryn-nav">
            <?php foreach ($menuItems as $item): ?>
                <?php $isActive = str_starts_with($currentUrl, $item['match']); ?>
                <a class="zoryn-nav-link <?= $isActive ? 'active' : ''; ?>" href="<?= $config['base_url'] . $item['url']; ?>">
                    <span class="zoryn-nav-icon"><i class="bi <?= $item['icon']; ?>"></i></span>
                    <span><?= htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="zoryn-sidebar-bottom">
            <div class="zoryn-user-card">
                <div class="zoryn-user-avatar"><?= htmlspecialchars(strtoupper(substr($_SESSION['user']['full_name'] ?? 'U', 0, 1))); ?></div>
                <div>
                    <div class="zoryn-user-name"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'User'); ?></div>
                    <div class="zoryn-user-role"><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Staff'); ?></div>
                </div>
            </div>
            <a class="zoryn-logout-link" href="<?= $config['base_url']; ?>/auth/logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</aside>
