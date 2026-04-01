<?php
class SupplierDeliveryLog extends Model
{
    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO supplier_delivery_logs (
                po_id, po_item_id, supplier_id, grocery_id, delivered_qty,
                delivery_date, expected_date, lead_days_actual, on_time_flag, notes, created_at
            ) VALUES (
                :po_id, :po_item_id, :supplier_id, :grocery_id, :delivered_qty,
                :delivery_date, :expected_date, :lead_days_actual, :on_time_flag, :notes, NOW()
            )
        ");
        $this->db->bind(':po_id', $data['po_id']);
        $this->db->bind(':po_item_id', $data['po_item_id']);
        $this->db->bind(':supplier_id', $data['supplier_id']);
        $this->db->bind(':grocery_id', $data['grocery_id']);
        $this->db->bind(':delivered_qty', $data['delivered_qty']);
        $this->db->bind(':delivery_date', $data['delivery_date']);
        $this->db->bind(':expected_date', $data['expected_date'] ?: null);
        $this->db->bind(':lead_days_actual', $data['lead_days_actual']);
        $this->db->bind(':on_time_flag', !empty($data['on_time_flag']) ? 1 : 0);
        $this->db->bind(':notes', $data['notes'] ?: null);
        return $this->db->execute();
    }

    public function getSupplierPerformance(): array
    {
        $this->db->query("
            SELECT
                s.supplier_name,
                COUNT(sdl.id) AS deliveries_count,
                COALESCE(SUM(sdl.delivered_qty), 0) AS total_delivered_qty,
                COALESCE(AVG(sdl.lead_days_actual), 0) AS avg_lead_days,
                COALESCE(SUM(CASE WHEN sdl.on_time_flag = 1 THEN 1 ELSE 0 END), 0) AS on_time_deliveries,
                CASE
                    WHEN COUNT(sdl.id) = 0 THEN 0
                    ELSE ROUND(SUM(CASE WHEN sdl.on_time_flag = 1 THEN 1 ELSE 0 END) / COUNT(sdl.id) * 100, 2)
                END AS on_time_rate
            FROM supplier_delivery_logs sdl
            INNER JOIN suppliers s ON s.id = sdl.supplier_id
            GROUP BY s.id, s.supplier_name
            ORDER BY on_time_rate DESC, avg_lead_days ASC, s.supplier_name ASC
        ");
        return $this->db->resultSet();
    }

    public function getRecentLogs(): array
    {
        $this->db->query("
            SELECT
                sdl.*,
                spo.po_no,
                s.supplier_name,
                g.grocery_name
            FROM supplier_delivery_logs sdl
            INNER JOIN supplier_purchase_orders spo ON spo.id = sdl.po_id
            INNER JOIN suppliers s ON s.id = sdl.supplier_id
            INNER JOIN groceries g ON g.id = sdl.grocery_id
            ORDER BY sdl.delivery_date DESC, sdl.id DESC
        ");
        return $this->db->resultSet();
    }
}
