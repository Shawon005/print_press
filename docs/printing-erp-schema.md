# Printing Press ERP Schema & Relationships

## Core Auth & Roles

### users (existing + extended)
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- name VARCHAR(255)
- email VARCHAR(255)
- phone VARCHAR(255) NULL
- password VARCHAR(255)
- status VARCHAR(255)
- last_login_at TIMESTAMP NULL
- remember_token VARCHAR(100) NULL
- created_at/updated_at TIMESTAMP NULL

### roles
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- name VARCHAR(255)
- guard_name VARCHAR(255)
- created_at/updated_at TIMESTAMP NULL

### model_has_roles
- role_id BIGINT UNSIGNED FK -> roles.id
- user_id BIGINT UNSIGNED FK -> users.id
- PK(role_id,user_id)

## CRM

### customers
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- customer_code VARCHAR(255)
- company_name VARCHAR(255)
- contact_person VARCHAR(255) NULL
- phone/email VARCHAR(255) NULL
- billing_address/delivery_address TEXT NULL
- city VARCHAR(255) NULL
- notes TEXT NULL
- outstanding_balance DECIMAL(15,2) DEFAULT 0
- last_trade_at DATE NULL
- status VARCHAR(255)
- deleted_at TIMESTAMP NULL
- created_at/updated_at TIMESTAMP NULL

## Printing Master

### paper_types
- id BIGINT UNSIGNED PK
- name VARCHAR(255) UNIQUE
- created_at/updated_at TIMESTAMP NULL

### supplier_paper_types
- id BIGINT UNSIGNED PK
- supplier_id BIGINT UNSIGNED FK -> suppliers.id
- paper_type_id BIGINT UNSIGNED FK -> paper_types.id
- UNIQUE(supplier_id,paper_type_id)
- created_at/updated_at TIMESTAMP NULL

## Job Orders

### job_orders
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- job_number VARCHAR(255) UNIQUE per tenant
- job_title VARCHAR(255)
- customer_id BIGINT UNSIGNED FK -> customers.id
- created_by BIGINT UNSIGNED FK -> users.id NULL
- order_date DATE NULL
- due_date DATE NULL
- status VARCHAR(255) (`draft`,`confirmed`,`in_production`,`quality_check`,`delivered`)
- gsm INT UNSIGNED
- paper_type_id BIGINT UNSIGNED FK -> paper_types.id
- ink_type VARCHAR(255)
- pantone_codes VARCHAR(255) NULL
- finish_type VARCHAR(255)
- total_pages INT UNSIGNED
- page_size VARCHAR(255)
- custom_width/custom_height DECIMAL(8,2) NULL
- total_copies INT UNSIGNED
- standard_sheet_size VARCHAR(255)
- colors TINYINT UNSIGNED
- printing_style VARCHAR(255)
- estimated_material_cost DECIMAL(15,2)
- estimated_plate_cost DECIMAL(15,2)
- estimated_other_cost DECIMAL(15,2)
- estimated_total_cost DECIMAL(15,2)
- estimated_unit_price DECIMAL(15,2)
- estimated_total_price DECIMAL(15,2)
- notes TEXT NULL
- created_at/updated_at TIMESTAMP NULL

### job_calculations
- id BIGINT UNSIGNED PK
- job_order_id BIGINT UNSIGNED FK -> job_orders.id
- pages_per_sheet INT UNSIGNED
- raw_sheets INT UNSIGNED
- wastage_percentage DECIMAL(5,2)
- wastage_sheets INT UNSIGNED
- total_sheets INT UNSIGNED
- reams INT UNSIGNED
- quires INT UNSIGNED
- remainder_sheets INT UNSIGNED
- input_snapshot JSON
- computed_at TIMESTAMP
- created_at/updated_at TIMESTAMP NULL

## Paper Inventory

### paper_stocks
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- paper_type_id BIGINT UNSIGNED FK -> paper_types.id
- gsm INT UNSIGNED
- sheet_size VARCHAR(255)
- stock_reams INT UNSIGNED
- stock_quires INT UNSIGNED
- stock_sheets INT UNSIGNED
- low_stock_threshold_sheets INT UNSIGNED
- UNIQUE(tenant_id,paper_type_id,gsm,sheet_size)
- created_at/updated_at TIMESTAMP NULL

### paper_stock_movements
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- paper_stock_id BIGINT UNSIGNED FK -> paper_stocks.id
- job_order_id BIGINT UNSIGNED FK -> job_orders.id NULL
- purchase_order_id BIGINT UNSIGNED FK -> purchase_orders.id NULL
- movement_type ENUM('stock_in','stock_out','adjustment')
- sheets INT UNSIGNED
- unit_cost DECIMAL(15,2) NULL
- remarks TEXT NULL
- created_by BIGINT UNSIGNED FK -> users.id NULL
- created_at/updated_at TIMESTAMP NULL

## CTP

### ctps
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- job_order_id BIGINT UNSIGNED FK -> job_orders.id
- plate_type VARCHAR(255)
- plate_size VARCHAR(255)
- quantity INT UNSIGNED
- cost_per_plate DECIMAL(15,2)
- total_plate_cost DECIMAL(15,2)
- issued_by BIGINT UNSIGNED FK -> users.id NULL
- issued_at TIMESTAMP
- created_at/updated_at TIMESTAMP NULL

## Financials

### job_payments
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- job_order_id BIGINT UNSIGNED FK -> job_orders.id
- payment_stage ENUM('advance','partial','final')
- amount DECIMAL(15,2)
- payment_date DATE
- payment_method VARCHAR(255) (`cash`,`bank`,`bKash`)
- reference_no VARCHAR(255) NULL
- notes TEXT NULL
- recorded_by BIGINT UNSIGNED FK -> users.id NULL
- created_at/updated_at TIMESTAMP NULL

## Suppliers & Procurement

### suppliers (extended)
- existing columns + outstanding_payable DECIMAL(15,2)

### purchase_orders (extended)
- existing columns
- job_order_id BIGINT UNSIGNED FK -> job_orders.id NULL
- is_auto_suggested BOOLEAN

## Delivery

### delivery_challans
- id BIGINT UNSIGNED PK
- tenant_id BIGINT UNSIGNED FK -> tenants.id
- job_order_id BIGINT UNSIGNED FK -> job_orders.id
- challan_number VARCHAR(255) UNIQUE per tenant
- delivery_date DATE
- receiver_name VARCHAR(255)
- signature_note TEXT NULL
- created_by BIGINT UNSIGNED FK -> users.id NULL
- created_at/updated_at TIMESTAMP NULL

## Eloquent Relationships

- User belongsTo Tenant; belongsToMany Role; belongsToMany Permission.
- Customer belongsTo Tenant; hasMany Quotation; hasMany Order; hasMany JobOrder.
- Supplier belongsTo Tenant; hasMany PurchaseOrder; belongsToMany PaperType.
- PaperType belongsToMany Supplier; hasMany JobOrder; hasMany PaperStock.
- JobOrder belongsTo Tenant, Customer, PaperType, User(creator); hasMany JobCalculation, JobPayment, Ctp, PaperStockMovement, DeliveryChallan.
- JobCalculation belongsTo JobOrder.
- PaperStock belongsTo Tenant and PaperType; hasMany PaperStockMovement.
- PaperStockMovement belongsTo PaperStock, JobOrder, PurchaseOrder, User(creator).
- Ctp belongsTo Tenant, JobOrder, User(issuer).
- JobPayment belongsTo Tenant, JobOrder, User(recorder).
- DeliveryChallan belongsTo Tenant, JobOrder, User(creator).
- PurchaseOrder belongsTo Tenant, Supplier, Warehouse, JobOrder; hasMany PurchaseOrderItem.

## Source of Truth
- Existing baseline migration: `database/migrations/2026_04_04_000003_create_printing_press_erp_tables.php`
- Printing-specific migration: `database/migrations/2026_04_26_120000_add_printing_press_core_tables.php`
