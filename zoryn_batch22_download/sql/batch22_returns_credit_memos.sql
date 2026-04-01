CREATE TABLE IF NOT EXISTS supplier_returns (
  id INT(11) NOT NULL AUTO_INCREMENT,
  return_no VARCHAR(50) NOT NULL,
  po_id INT(11) DEFAULT NULL,
  po_item_id INT(11) DEFAULT NULL,
  supplier_id INT(11) NOT NULL,
  grocery_id INT(11) NOT NULL,
  return_date DATE NOT NULL,
  return_type ENUM('damaged','short_shipment','wrong_item','over_delivery','quality_issue','other') NOT NULL DEFAULT 'damaged',
  quantity DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  line_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('pending','approved','replaced','credited','closed') NOT NULL DEFAULT 'pending',
  notes VARCHAR(255) DEFAULT NULL,
  created_by INT(11) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY uq_supplier_return_no (return_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS supplier_credit_memos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  memo_no VARCHAR(50) NOT NULL,
  supplier_id INT(11) NOT NULL,
  return_id INT(11) DEFAULT NULL,
  purchase_id INT(11) DEFAULT NULL,
  memo_date DATE NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  applied_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  balance_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('open','partial','applied','void') NOT NULL DEFAULT 'open',
  notes VARCHAR(255) DEFAULT NULL,
  created_by INT(11) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY uq_supplier_credit_memo_no (memo_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE grocery_purchases
  ADD COLUMN supplier_credit_applied DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER amount_paid;
