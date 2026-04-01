<?php
class SupplierCreditMemo extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT scm.*, s.supplier_name, sr.return_no, gp.purchase_no
            FROM supplier_credit_memos scm
            INNER JOIN suppliers s ON s.id = scm.supplier_id
            LEFT JOIN supplier_returns sr ON sr.id = scm.return_id
            LEFT JOIN grocery_purchases gp ON gp.id = scm.purchase_id
            ORDER BY scm.id DESC
        ");
        return $this->db->resultSet();
    }

    public function create(array $data): int|false
    {
        $memoNo = 'CM-' . date('Ymd-His') . '-' . rand(100, 999);

        $this->db->query("
            INSERT INTO supplier_credit_memos (
                memo_no, supplier_id, return_id, purchase_id, memo_date, amount,
                applied_amount, balance_amount, status, notes, created_by, created_at
            ) VALUES (
                :memo_no, :supplier_id, :return_id, :purchase_id, :memo_date, :amount,
                0, :balance_amount, 'open', :notes, :created_by, NOW()
            )
        ");
        $this->db->bind(':memo_no', $memoNo);
        $this->db->bind(':supplier_id', $data['supplier_id']);
        $this->db->bind(':return_id', $data['return_id'] ?: null);
        $this->db->bind(':purchase_id', $data['purchase_id'] ?: null);
        $this->db->bind(':memo_date', $data['memo_date']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':balance_amount', $data['amount']);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':created_by', $data['created_by'] ?? null);

        if (!$this->db->execute()) return false;
        return (int)$this->db->lastInsertId();
    }

    public function applyToPurchase(int $memoId, int $purchaseId, float $applyAmount): bool
    {
        $this->db->query("
            SELECT amount, applied_amount, balance_amount
            FROM supplier_credit_memos
            WHERE id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $memoId);
        $memo = $this->db->single();
        if (!$memo) return false;

        $balance = (float)($memo['balance_amount'] ?? 0);
        if ($applyAmount <= 0) return false;
        if ($applyAmount > $balance) $applyAmount = $balance;

        $newApplied = (float)($memo['applied_amount'] ?? 0) + $applyAmount;
        $newBalance = max(0, (float)($memo['amount'] ?? 0) - $newApplied);
        $status = 'partial';
        if ($newBalance <= 0) $status = 'applied';
        elseif ($newApplied <= 0) $status = 'open';

        $this->db->query("
            UPDATE supplier_credit_memos
            SET purchase_id = :purchase_id,
                applied_amount = :applied_amount,
                balance_amount = :balance_amount,
                status = :status
            WHERE id = :id
        ");
        $this->db->bind(':purchase_id', $purchaseId);
        $this->db->bind(':applied_amount', $newApplied);
        $this->db->bind(':balance_amount', $newBalance);
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $memoId);
        return $this->db->execute();
    }

    public function getOpenBySupplier(int $supplierId): array
    {
        $this->db->query("
            SELECT *
            FROM supplier_credit_memos
            WHERE supplier_id = :supplier_id
              AND balance_amount > 0
              AND status IN ('open','partial')
            ORDER BY memo_date ASC, id ASC
        ");
        $this->db->bind(':supplier_id', $supplierId);
        return $this->db->resultSet();
    }

    public function getSummary(): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_memos,
                SUM(CASE WHEN status IN ('open','partial') THEN 1 ELSE 0 END) AS open_memos,
                COALESCE(SUM(amount), 0) AS total_memo_amount,
                COALESCE(SUM(balance_amount), 0) AS total_open_balance
            FROM supplier_credit_memos
        ");
        return $this->db->single() ?: [
            'total_memos' => 0,
            'open_memos' => 0,
            'total_memo_amount' => 0,
            'total_open_balance' => 0,
        ];
    }
}
