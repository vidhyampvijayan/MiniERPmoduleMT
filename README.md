# Mini ERP Module



## Setup Commands

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve


## Test Execution Instructions

Run test cases with artisan:

php artisan test


## Default Login Credentials

Credentials for both Web and API authentication:

Email: `admin@minierp.test`
Password: `password`

Web portal: `http://127.0.0.1:8000/login`
API login endpoint: `POST /api/login`

---

## Indexing & Optimization Decisions

1. **`products` table**:
   - `sku`: Unique index for fast barcode / SKU lookup.
   - `['is_active', 'stock_quantity']`: Composite index to speed up low stock query count (`stock_quantity < 10`).

2. **`purchase_orders` table**:
   - `order_number`: Unique index.
   - `['supplier_id', 'status']`: Composite index to speed up aggregate query for supplier spend report on received orders (`status = 'RECEIVED'`).
   - `['status', 'created_at']`: Composite index for latest orders query on the dashboard.

3. **`purchase_order_items` table**:
   - `['purchase_order_id', 'product_id']`: Composite index for line item lookups and relationship joins.

4. **Data Integrity & Concurrency**:
   - Stock update during transition to `RECEIVED` is wrapped in `DB::transaction()` using `lockForUpdate()` on product records to prevent race conditions.
   - Used eager loading (`with(['supplier', 'items.product'])`) to prevent N+1 query issues.

---

## Sample cURL Commands

### 1. API Login

curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@minierp.test", "password": "password"}'


### 2. Get Inventory

curl -X GET http://127.0.0.1:8000/api/inventory \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"


### 3. Get Low Stock Items (< 10 units)

curl -X GET "http://127.0.0.1:8000/api/inventory/low-stock?threshold=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"


### 4. Create Purchase Order (Draft)

curl -X POST http://127.0.0.1:8000/api/purchase-orders \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "supplier_id": 1,
    "notes": "test order",
    "items": [
      {
        "product_id": 1,
        "quantity": 10,
        "unit_price": 50.00
      }
    ]
  }'


### 5. Update Status (Approved / Received / Cancelled)

# Approve
curl -X PATCH http://127.0.0.1:8000/api/purchase-orders/1/status \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{"status": "APPROVED"}'

# Mark as Received
curl -X PATCH http://127.0.0.1:8000/api/purchase-orders/1/status \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{"status": "RECEIVED"}'


### 6. Supplier Spend Report

curl -X GET http://127.0.0.1:8000/api/reports/supplier-spend \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"


---

## Postman Collection

Exported Postman collection file is available at:
`postman_collection.json`
