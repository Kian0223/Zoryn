ZORYN BATCH 21 INSTALLATION
===========================

This batch adds:
- Partial PO receiving by line
- Open PO balance tracking
- Supplier delivery logs
- Supplier delivery performance report

INSTALL ORDER
-------------
1. Run: sql/batch21_partial_receiving_delivery_performance.sql
2. Copy the PHP files into your project folders
3. Merge public/assets/css/batch21.css if you want the extra styling

ROUTES ADDED / UPDATED
----------------------
/supplierpurchaseorders/receiveLine/{itemId}

NOTES
-----
- PO lines now track received quantity separately from ordered quantity.
- PO status auto-refreshes to issued, partially_received, or received based on line balances.
- Supplier delivery logs track delivered quantity, delivery date, expected date, and on-time performance.
