<?php
class Sale extends Model
{
    public function createSaleReturningId(array $header, array $items): int|false
    {
        $receiptNo = 'REC-' . date('Ymd-His') . '-' . rand(100, 999);
        $saleDate = date('Y-m-d H:i:s');
        $grossAmount = 0;

        foreach ($items as $item) {
            $grossAmount += (float)($item['line_total'] ?? 0);
        }

        $paymentMethod = $header['payment_method'] ?? 'cash';
        $paymentReference = trim((string)($header['payment_reference'] ?? '')) ?: null;
        $customerId = $header['customer_id'] ?? null;
        $pointsEarned = isset($header['loyalty_points_earned']) ? (float)$header['loyalty_points_earned'] : 0;
        $pointsRedeemed = isset($header['loyalty_points_redeemed']) ? (float)$header['loyalty_points_redeemed'] : 0;
        $promoId = $header['promo_id'] ?? null;
        $promoDiscount = isset($header['promo_discount_amount']) ? (float)$header['promo_discount_amount'] : 0;

        $netTotal = max(0, $grossAmount - $pointsRedeemed - $promoDiscount);
        $paidAmount = isset($header['paid_amount']) ? (float)$header['paid_amount'] : $netTotal;
        $changeAmount = max(0, $paidAmount - $netTotal);

        $this->db->query("
            INSERT INTO sales (
                receipt_no, sale_date, total_amount, payment_method, payment_reference,
                paid_amount, change_amount, sale_status, created_by, customer_id,
                loyalty_points_earned, loyalty_points_redeemed, promo_id, promo_discount_amount
            ) VALUES (
                :receipt_no, :sale_date, :total_amount, :payment_method, :payment_reference,
                :paid_amount, :change_amount, 'completed', :created_by, :customer_id,
                :loyalty_points_earned, :loyalty_points_redeemed, :promo_id, :promo_discount_amount
            )
        ");
        $this->db->bind(':receipt_no', $receiptNo);
        $this->db->bind(':sale_date', $saleDate);
        $this->db->bind(':total_amount', $netTotal);
        $this->db->bind(':payment_method', $paymentMethod);
        $this->db->bind(':payment_reference', $paymentReference);
        $this->db->bind(':paid_amount', $paidAmount);
        $this->db->bind(':change_amount', $changeAmount);
        $this->db->bind(':created_by', $header['created_by'] ?? null);
        $this->db->bind(':customer_id', $customerId);
        $this->db->bind(':loyalty_points_earned', $pointsEarned);
        $this->db->bind(':loyalty_points_redeemed', $pointsRedeemed);
        $this->db->bind(':promo_id', $promoId);
        $this->db->bind(':promo_discount_amount', $promoDiscount);

        if (!$this->db->execute()) {
            return false;
        }

        $saleId = (int)$this->db->lastInsertId();

        foreach ($items as $item) {
            $this->db->query("
                INSERT INTO sale_items (sale_id, viand_id, product_id, quantity, unit_price, line_total)
                VALUES (:sale_id, :viand_id, :product_id, :quantity, :unit_price, :line_total)
            ");
            $this->db->bind(':sale_id', $saleId);
            $this->db->bind(':viand_id', $item['viand_id'] ?? null);
            $this->db->bind(':product_id', $item['product_id'] ?? null);
            $this->db->bind(':quantity', $item['quantity'] ?? 0);
            $this->db->bind(':unit_price', $item['unit_price'] ?? 0);
            $this->db->bind(':line_total', $item['line_total'] ?? 0);

            if (!$this->db->execute()) {
                return false;
            }
        }

        return $saleId;
    }

    public function getTodaySalesAmount(): float
    {
        $this->db->query("
            SELECT COALESCE(SUM(total_amount), 0) AS total
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
              AND (sale_status IS NULL OR sale_status != 'cancelled')
        ");
        $row = $this->db->single();

        return (float)($row->total ?? 0);
    }

    public function getTodaySalesCount(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
              AND (sale_status IS NULL OR sale_status != 'cancelled')
        ");
        $row = $this->db->single();

        return (int)($row->total ?? 0);
    }

    public function getTotalSalesCount(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM sales
            WHERE sale_status IS NULL OR sale_status != 'cancelled'
        ");
        $row = $this->db->single();

        return (int)($row->total ?? 0);
    }

    public function getRangeSummary(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                COALESCE(SUM(total_amount), 0) AS total_sales,
                COUNT(*) AS total_transactions
            FROM sales
            WHERE DATE(sale_date) BETWEEN :start_date AND :end_date
              AND (sale_status IS NULL OR sale_status != 'cancelled')
        ");
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);

        $row = $this->db->single();

        return [
            'total_sales' => (float)($row->total_sales ?? 0),
            'total_transactions' => (int)($row->total_transactions ?? 0),
        ];
    }

    public function getSalesWithItems(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                s.id,
                s.receipt_no,
                s.sale_date,
                s.total_amount,
                s.payment_method,
                s.sale_status,
                COALESCE(COUNT(si.id), 0) AS item_count
            FROM sales s
            LEFT JOIN sale_items si ON si.sale_id = s.id
            WHERE DATE(s.sale_date) BETWEEN :start_date AND :end_date
            GROUP BY s.id, s.receipt_no, s.sale_date, s.total_amount, s.payment_method, s.sale_status
            ORDER BY s.sale_date DESC, s.id DESC
        ");
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getTopSellingItems(string $startDate, string $endDate, int $limit = 5): array
    {
        $limit = max(1, (int)$limit);

        $sql = "
            SELECT
                CASE
                    WHEN si.viand_id IS NOT NULL THEN CONCAT('Viand #', si.viand_id)
                    WHEN si.product_id IS NOT NULL THEN CONCAT('Product #', si.product_id)
                    ELSE 'Unknown Item'
                END AS item_name,
                CASE
                    WHEN si.viand_id IS NOT NULL THEN 'Viand'
                    WHEN si.product_id IS NOT NULL THEN 'Product'
                    ELSE 'Item'
                END AS item_type,
                COALESCE(SUM(si.quantity), 0) AS qty_sold,
                COALESCE(SUM(si.line_total), 0) AS total_sales
            FROM sale_items si
            INNER JOIN sales s ON s.id = si.sale_id
            WHERE DATE(s.sale_date) BETWEEN :start_date AND :end_date
              AND (s.sale_status IS NULL OR s.sale_status != 'cancelled')
            GROUP BY
                si.viand_id,
                si.product_id,
                CASE
                    WHEN si.viand_id IS NOT NULL THEN CONCAT('Viand #', si.viand_id)
                    WHEN si.product_id IS NOT NULL THEN CONCAT('Product #', si.product_id)
                    ELSE 'Unknown Item'
                END,
                CASE
                    WHEN si.viand_id IS NOT NULL THEN 'Viand'
                    WHEN si.product_id IS NOT NULL THEN 'Product'
                    ELSE 'Item'
                END
            ORDER BY qty_sold DESC, total_sales DESC
            LIMIT {$limit}
        ";

        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getRevenueTrend(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                DATE(sale_date) AS sale_day,
                COALESCE(SUM(total_amount), 0) AS revenue
            FROM sales
            WHERE DATE(sale_date) BETWEEN :start_date AND :end_date
              AND (sale_status IS NULL OR sale_status != 'cancelled')
            GROUP BY DATE(sale_date)
            ORDER BY DATE(sale_date) ASC
        ");
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getOrdersByHour(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                HOUR(sale_date) AS order_hour,
                COUNT(*) AS total_orders,
                COALESCE(SUM(total_amount), 0) AS total_sales
            FROM sales
            WHERE DATE(sale_date) BETWEEN :start_date AND :end_date
              AND (sale_status IS NULL OR sale_status != 'cancelled')
            GROUP BY HOUR(sale_date)
            ORDER BY HOUR(sale_date) ASC
        ");
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }
}