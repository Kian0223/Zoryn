# Zoryn Restaurant System - Batch 3 (MVC)

Batch 3 adds:
- Users CRUD
- Sales / POS entry page
- Sales report with date filter
- Dashboard stats updates

## Installation
1. Copy `ZorynRestaurant` into `C:/xampp/htdocs/`
2. Import `database/zoryn_restaurant.sql`
3. Edit `config/database.php` if needed
4. Open `http://localhost/ZorynRestaurant`

## Default Login
- Username: `admin`
- Password: `admin123`

## Notes
- Product sales automatically deduct product stock.
- Viands are sold from recipe definitions and use the selling price saved in viands.
- Grocery costing still drives recipe cost visibility for each viand.
