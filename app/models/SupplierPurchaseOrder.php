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
            SELECT spoi.*, g.grocery_name, g.unit,
                   (spoi.ordered_qty - spoi.received_qty) AS balance_qty
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

    public function updateStatus(int $poId, string $status): bool
    {
        $this->db->query("UPDATE supplier_purchase_orders SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $poId);
        return $this->db->execute();
    }

    public function receiveLine(int $itemId, float $deliveredQty): bool
    {
        $this->db->query("
            SELECT po_id, ordered_qty, received_qty
            FROM supplier_purchase_order_items
            WHERE id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $itemId);
        $item = $this->db->single();
        if (!$item) return false;

        $ordered = (float)($item['ordered_qty'] ?? 0);
        $received = (float)($item['received_qty'] ?? 0);
        $newReceived = $received + max(0, $deliveredQty);
        if ($newReceived > $ordered) $newReceived = $ordered;

        $lineStatus = 'open';
        if ($newReceived <= 0) {
            $lineStatus = 'open';
        } elseif ($newReceived < $ordered) {
            $lineStatus = 'partial';
        } else {
            $lineStatus = 'received';
        }

        $this->db->query("
            UPDATE supplier_purchase_order_items
            SET received_qty = :received_qty,
                last_received_at = NOW(),
                line_status = :line_status
            WHERE id = :id
        ");
        $this->db->bind(':received_qty', $newReceived);
        $this->db->bind(':line_status', $lineStatus);
        $this->db->bind(':id', $itemId);
        $ok = $this->db->execute();

        if ($ok) {
            $this->refreshPOStatus((int)$item['po_id']);
        }

        return $ok;
    }

    public function refreshPOStatus(int $poId): bool
    {
        $this->db->query("
            SELECT
                SUM(CASE WHEN line_status = 'received' THEN 1 ELSE 0 END) AS received_lines,
                SUM(CASE WHEN line_status = 'partial' THEN 1 ELSE 0 END) AS partial_lines,
                SUM(CASE WHEN line_status = 'open' THEN 1 ELSE 0 END) AS open_lines,
                COUNT(*) AS total_lines
            FROM supplier_purchase_order_items
            WHERE po_id = :po_id
        ");
        $this->db->bind(':po_id', $poId);
        $summary = $this->db->single() ?: [];

        $status = 'issued';
        $receivedLines = (int)($summary['received_lines'] ?? 0);
        $partialLines = (int)($summary['partial_lines'] ?? 0);
        $openLines = (int)($summary['open_lines'] ?? 0);
        $totalLines = (int)($summary['total_lines'] ?? 0);

        if ($totalLines > 0 && $receivedLines === $totalLines) {
            $status = 'received';
        } elseif ($partialLines > 0 || ($receivedLines > 0 && $openLines > 0)) {
            $status = 'partially_received';
        } else {
            $status = 'issued';
        }

        $this->db->query("UPDATE supplier_purchase_orders SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $poId);
        return $this->db->execute();
    }

    public function getOpenBalances(): array
    {
        $this->db->query("
            SELECT
                spo.id AS po_id,
                spoi.id AS po_item_id,
                spo.po_no,
                spo.po_date,
                spo.expected_date,
                spo.status AS po_status,
                s.id AS supplier_id,
                s.supplier_name,
                g.id AS grocery_id,
                g.grocery_name,
                g.unit,
                spoi.ordered_qty,
                spoi.received_qty,
                (spoi.ordered_qty - spoi.received_qty) AS balance_qty,
                spoi.unit_cost,
                spoi.line_status
            FROM supplier_purchase_order_items spoi
            INNER JOIN supplier_purchase_orders spo ON spo.id = spoi.po_id
            INNER JOIN suppliers s ON s.id = spo.supplier_id
            INNER JOIN groceries g ON g.id = spoi.grocery_id
            WHERE spoi.ordered_qty > spoi.received_qty
            ORDER BY spo.po_date DESC, s.supplier_name ASC, g.grocery_name ASC
        ");
        return $this->db->resultSet();
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
