ZORYN BATCH 22 INSTALLATION
===========================

This batch adds:
- Supplier returns
- Damaged / short shipment handling
- Supplier credit memos
- Credit application to purchases

INSTALL ORDER
-------------
1. Run: sql/batch22_returns_credit_memos.sql
2. Copy the PHP files into your project folders
3. Merge public/assets/css/batch22.css if you want the extra styling

ROUTES ADDED
------------
/supplierreturns/index
/supplierreturns/store
/supplierreturns/approve/{id}
/supplierreturns/credit/{id}
/supplierreturns/applyCredit

NOTES
-----
- Returns can be logged against PO lines and suppliers.
- Credit memos can be generated from approved returns.
- Credit memos can be applied to grocery purchases to reduce balance_due.
- This batch uses manual purchase_id entry when applying credits.
