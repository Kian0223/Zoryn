<?php
class SupplierPurchaseOrder extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT spo.*, s.supplier_name, pp.plan_no, u.full_name AS creator_name
            FROM supplier_purchase_orders spo
            INNER JOIN suppliers s ON s.id = spo.supplier_id
            LEFT JOIN purchase_plans pp ON pp.id = spo.plan_id
            LEFT JOIN users u ON u.id = spo.created_by
            ORDER BY spo.id DESC
        ");
        $rows = $this->db->resultSet();
        foreach ($rows as &$row) {
            $row['items'] = $this->getItems((int)$row['id']);
        }
        return $rows;
    }

    public function getItems(int $poId): array
    {
        $this->db->query("
            SELECT spoi.*, g.grocery_name, g.unit
            FROM supplier_purchase_order_items spoi
            INNER JOIN groceries g ON g.id = spoi.grocery_id
            WHERE spoi.po_id = :po_id
            ORDER BY g.grocery_name ASC
        ");
        $this->db->bind(':po_id', $poId);
        return $this->db->resultSet();
    }

    public function findById(int $id): array|false
    {
        $this->db->query("
            SELECT spo.*, s.supplier_name, s.contact_person, s.phone, s.email, s.address, pp.plan_no
            FROM supplier_purchase_orders spo
            INNER JOIN suppliers s ON s.id = spo.supplier_id
            LEFT JOIN purchase_plans pp ON pp.id = spo.plan_id
            WHERE spo.id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        if (!$row) return false;
        $row['items'] = $this->getItems($id);
        return $row;
    }

    public function createFromPlanBySupplier(int $planId, int $supplierId, array $items, ?int $createdBy, ?string $expectedDate = null, ?string $notes = null): int|false
    {
        $poNo = 'PO-' . date('Ymd-His') . '-' . rand(100,999);

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float)($item['approved_qty'] ?? 0) * (float)($item['unit_cost'] ?? 0);
        }

        $this->db->query("
            INSERT INTO supplier_purchase_orders (
                po_no, plan_id, supplier_id, po_date, expected_date, status, subtotal, notes, created_by, created_at
            ) VALUES (
                :po_no, :plan_id, :supplier_id, CURDATE(), :expected_date, 'draft', :subtotal, :notes, :created_by, NOW()
            )
        ");
        $this->db->bind(':po_no', $poNo);
        $this->db->bind(':plan_id', $planId);
        $this->db->bind(':supplier_id', $supplierId);
        $this->db->bind(':expected_date', $expectedDate ?: null);
        $this->db->bind(':subtotal', $subtotal);
        $this->db->bind(':notes', $notes ?: null);
        $this->db->bind(':created_by', $createdBy);

        if (!$this->db->execute()) return false;
        $poId = (int)$this->db->lastInsertId();

        foreach ($items as $item) {
            $orderedQty = (float)($item['approved_qty'] ?? 0);
            $unitCost = (float)($item['unit_cost'] ?? 0);
            if ($orderedQty <= 0) continue;

            $this->db->query("
                INSERT INTO supplier_purchase_order_items (
                    po_id, plan_item_id, grocery_id, ordered_qty, received_qty, unit_cost, line_total
                ) VALUES (
                    :po_id, :plan_item_id, :grocery_id, :ordered_qty, 0, :unit_cost, :line_total
                )
            ");
            $this->db->bind(':po_id', $poId);
            $this->db->bind(':plan_item_id', $item['id'] ?? null);
            $this->db->bind(':grocery_id', $item['grocery_id']);
            $this->db->bind(':ordered_qty', $orderedQty);
            $this->db->bind(':unit_cost', $unitCost);
            $this->db->bind(':line_total', $orderedQty * $unitCost);
            if (!$this->db->execute()) return false;
        }

        return $poId;
    }

    public function updateStatus(int $poId, string $status): bool
    {
        $this->db->query("UPDATE supplier_purchase_orders SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $poId);
        return $this->db->execute();
    }

    public function updateReceivedQty(int $itemId, float $receivedQty): bool
    {
        $this->db->query("
            UPDATE supplier_purchase_order_items
            SET received_qty = :received_qty
            WHERE id = :id
        ");
        $this->db->bind(':received_qty', $receivedQty);
        $this->db->bind(':id', $itemId);
        return $this->db->execute();
    }

    public function getSummary(): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_pos,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft_pos,
                SUM(CASE WHEN status = 'issued' THEN 1 ELSE 0 END) AS issued_pos,
                SUM(CASE WHEN status = 'partially_received' THEN 1 ELSE 0 END) AS partially_received_pos,
                SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) AS received_pos
            FROM supplier_purchase_orders
        ");
        return $this->db->single() ?: [
            'total_pos'=>0,'draft_pos'=>0,'issued_pos'=>0,'partially_received_pos'=>0,'received_pos'=>0
        ];
    }
}
