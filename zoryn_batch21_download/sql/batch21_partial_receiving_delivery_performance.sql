ALTER TABLE supplier_purchase_order_items
  ADD COLUMN last_received_at DATETIME DEFAULT NULL AFTER received_qty,
  ADD COLUMN line_status ENUM('open','partial','received','cancelled') NOT NULL DEFAULT 'open' AFTER last_received_at;

CREATE TABLE IF NOT EXISTS supplier_delivery_logs (
  id INT(11) NOT NULL AUTO_INCREMENT,
  po_id INT(11) NOT NULL,
  po_item_id INT(11) NOT NULL,
  supplier_id INT(11) NOT NULL,
  grocery_id INT(11) NOT NULL,
  delivered_qty DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  delivery_date DATE NOT NULL,
  expected_date DATE DEFAULT NULL,
  lead_days_actual INT(11) DEFAULT NULL,
  on_time_flag TINYINT(1) NOT NULL DEFAULT 0,
  notes VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
