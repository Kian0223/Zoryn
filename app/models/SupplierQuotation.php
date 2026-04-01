<?php
class SupplierQuotation extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT sq.*, g.grocery_name, g.unit, s.supplier_name
            FROM supplier_quotations sq
            INNER JOIN groceries g ON g.id = sq.grocery_id
            INNER JOIN suppliers s ON s.id = sq.supplier_id
            ORDER BY sq.quote_date DESC, g.grocery_name ASC
        ");
        return $this->db->resultSet();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO supplier_quotations (
                grocery_id, supplier_id, quoted_price, lead_time_days, min_order_qty, quote_date, notes, created_at
            ) VALUES (
                :grocery_id, :supplier_id, :quoted_price, :lead_time_days, :min_order_qty, :quote_date, :notes, NOW()
            )
        ");
        $this->db->bind(':grocery_id', $data['grocery_id']);
        $this->db->bind(':supplier_id', $data['supplier_id']);
        $this->db->bind(':quoted_price', $data['quoted_price']);
        $this->db->bind(':lead_time_days', $data['lead_time_days']);
        $this->db->bind(':min_order_qty', $data['min_order_qty']);
        $this->db->bind(':quote_date', $data['quote_date']);
        $this->db->bind(':notes', $data['notes'] ?: null);
        return $this->db->execute();
    }

    public function getComparisonRows(): array
    {
        $this->db->query("
            SELECT
                g.id AS grocery_id,
                g.grocery_name,
                g.unit,
                s.supplier_name,
                sq.quoted_price,
                sq.lead_time_days,
                sq.min_order_qty,
                sq.quote_date,
                ROW_NUMBER() OVER (PARTITION BY g.id ORDER BY sq.quoted_price ASC, sq.lead_time_days ASC, sq.quote_date DESC) AS quote_rank
            FROM supplier_quotations sq
            INNER JOIN groceries g ON g.id = sq.grocery_id
            INNER JOIN suppliers s ON s.id = sq.supplier_id
            ORDER BY g.grocery_name ASC, sq.quoted_price ASC, sq.lead_time_days ASC
        ");
        return $this->db->resultSet();
    }
}
