<?php require APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex">
    <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>

    <main class="content-area flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Business Analytics</h2>
        </div>

        <?php require APP_PATH . '/views/partials/alerts.php'; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="analytics-card card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Sales</div>
                        <h3 class="mb-0">₱<?= number_format((float)($totals['total_sales'] ?? 0), 2); ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="analytics-card card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Expenses</div>
                        <h3 class="mb-0">₱<?= number_format((float)($totals['total_expenses'] ?? 0), 2); ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="analytics-card card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Profit</div>
                        <h3 class="mb-0">₱<?= number_format((float)($totals['total_profit'] ?? 0), 2); ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="analytics-card card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Orders</div>
                        <h3 class="mb-0"><?= (int)($totals['total_orders'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4 align-items-start">
            <div class="col-lg-8">
                <div class="analytics-card card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Monthly Sales vs Expenses vs Profit</h5>

                        <div class="analytics-chart-wrap analytics-chart-wrap-lg">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="analytics-card card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="mb-3">This Month Snapshot</h5>

                        <div class="mb-3">
                            <div class="text-muted small">Sales</div>
                            <div class="fs-4">₱<?= number_format((float)($snapshot['current_month_sales'] ?? 0), 2); ?></div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Expenses</div>
                            <div class="fs-4">₱<?= number_format((float)($snapshot['current_month_expenses'] ?? 0), 2); ?></div>
                        </div>

                        <div>
                            <div class="text-muted small">Profit</div>
                            <div class="fs-4">₱<?= number_format((float)($snapshot['current_month_profit'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="analytics-card card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Top Suppliers</h5>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Purchases</th>
                                        <th>Total Purchased</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($top_suppliers ?? []) as $supplier): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($supplier['supplier_name'] ?? '-'); ?></td>
                                            <td><?= (int)($supplier['purchase_count'] ?? 0); ?></td>
                                            <td>₱<?= number_format((float)($supplier['total_purchased'] ?? 0), 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if (empty($top_suppliers)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No supplier data found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="analytics-card card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Top Expense Categories</h5>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Entries</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($top_expense_categories ?? []) as $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['category_name'] ?? '-'); ?></td>
                                            <td><?= (int)($category['entry_count'] ?? 0); ?></td>
                                            <td>₱<?= number_format((float)($category['total_amount'] ?? 0), 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if (empty($top_expense_categories)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No expense data found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="analytics-card card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">Sales by Payment Method</h5>

                <div class="analytics-chart-wrap analytics-chart-wrap-sm">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.analytics-chart-wrap {
    position: relative;
    width: 100%;
    min-height: 0;
}

.analytics-chart-wrap-lg {
    height: 340px;
}

.analytics-chart-wrap-sm {
    height: 280px;
}

@media (max-width: 991.98px) {
    .analytics-chart-wrap-lg {
        height: 300px;
    }

    .analytics-chart-wrap-sm {
        height: 240px;
    }
}

.analytics-chart-wrap canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthlyData = <?= json_encode($monthly ?? []); ?>;
const monthlyLabels = monthlyData.map(row => row.month_key ?? '');
const monthlySales = monthlyData.map(row => parseFloat(row.sales_total ?? 0));
const monthlyExpenses = monthlyData.map(row => parseFloat(row.expense_total ?? 0));
const monthlyProfit = monthlyData.map(row => parseFloat(row.profit_total ?? 0));

const paymentData = <?= json_encode($payment_methods ?? []); ?>;
const paymentLabels = paymentData.map(row => (row.payment_method ?? '').toUpperCase());
const paymentTotals = paymentData.map(row => parseFloat(row.total_amount ?? 0));

const monthlyCanvas = document.getElementById('monthlyChart');
if (monthlyCanvas) {
    new Chart(monthlyCanvas, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [
                {
                    label: 'Sales',
                    data: monthlySales,
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: monthlyExpenses,
                    borderWidth: 1
                },
                {
                    label: 'Profit',
                    data: monthlyProfit,
                    type: 'line',
                    borderWidth: 2,
                    tension: 0.25,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

const paymentCanvas = document.getElementById('paymentChart');
if (paymentCanvas) {
    new Chart(paymentCanvas, {
        type: 'doughnut',
        data: {
            labels: paymentLabels,
            datasets: [
                {
                    data: paymentTotals,
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
}
</script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>