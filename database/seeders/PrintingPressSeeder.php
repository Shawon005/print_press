<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerInteraction;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InventoryStock;
use App\Models\JobStage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialCategory;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrintingPressSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $starter = SubscriptionPlan::create([
                'name' => 'Starter',
                'code' => 'starter',
                'monthly_price' => 29,
                'yearly_price' => 299,
                'max_users' => 5,
                'max_orders_per_month' => 500,
                'max_warehouses' => 1,
                'max_storage_mb' => 2048,
                'features_json' => ['basic_reports' => true, 'delivery_tracking' => false],
            ]);

            $growth = SubscriptionPlan::create([
                'name' => 'Growth',
                'code' => 'growth',
                'monthly_price' => 79,
                'yearly_price' => 799,
                'max_users' => 15,
                'max_orders_per_month' => 3000,
                'max_warehouses' => 3,
                'max_storage_mb' => 8192,
                'features_json' => ['basic_reports' => true, 'delivery_tracking' => true, 'crm' => true],
            ]);

            $business = SubscriptionPlan::create([
                'name' => 'Business',
                'code' => 'business',
                'monthly_price' => 149,
                'yearly_price' => 1499,
                'max_users' => 999,
                'max_orders_per_month' => null,
                'max_warehouses' => null,
                'max_storage_mb' => null,
                'features_json' => ['advanced_reporting' => true, 'production_workflow' => true, 'ai_ready' => true],
            ]);

            $tenant = Tenant::create([
                'name' => 'PrintFlow Graphics',
                'slug' => 'printflow-graphics',
                'email' => 'hello@printflow.test',
                'phone' => '+880170000000',
                'address' => 'Tejgaon Industrial Area, Dhaka',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'subscription_plan_id' => $growth->id,
                'subscription_status' => 'active',
                'trial_ends_at' => now()->addDays(14),
                'is_active' => true,
            ]);

            $permissionNames = [
                'dashboard.view', 'customers.manage', 'suppliers.manage', 'products.manage', 'inventory.manage',
                'quotations.manage', 'orders.manage', 'purchases.manage', 'invoices.manage', 'expenses.manage',
                'deliveries.manage', 'reports.view', 'users.manage', 'settings.manage', 'subscription.manage',
            ];

            foreach ($permissionNames as $permissionName) {
                Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
            }

            $roleMap = [];

            foreach ([
                'Tenant Owner', 'Manager', 'Sales / CRM', 'Production Staff',
                'Inventory Manager', 'Accountant', 'Delivery Staff',
            ] as $roleName) {
                $roleMap[$roleName] = Role::create([
                    'tenant_id' => $tenant->id,
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);
            }

            $roleMap['Tenant Owner']->permissions()->sync(Permission::pluck('id'));
            $roleMap['Manager']->permissions()->sync(Permission::whereIn('name', [
                'dashboard.view', 'customers.manage', 'suppliers.manage', 'products.manage', 'inventory.manage',
                'quotations.manage', 'orders.manage', 'purchases.manage', 'reports.view',
            ])->pluck('id'));
            $roleMap['Sales / CRM']->permissions()->sync(Permission::whereIn('name', [
                'dashboard.view', 'customers.manage', 'quotations.manage', 'orders.manage',
            ])->pluck('id'));
            $roleMap['Inventory Manager']->permissions()->sync(Permission::whereIn('name', [
                'dashboard.view', 'inventory.manage', 'suppliers.manage', 'purchases.manage',
            ])->pluck('id'));
            $roleMap['Accountant']->permissions()->sync(Permission::whereIn('name', [
                'dashboard.view', 'invoices.manage', 'expenses.manage', 'reports.view',
            ])->pluck('id'));
            $roleMap['Delivery Staff']->permissions()->sync(Permission::whereIn('name', [
                'dashboard.view', 'deliveries.manage',
            ])->pluck('id'));

            $users = [
                ['name' => 'Shahadat Rahman', 'email' => 'owner@printflow.test', 'phone' => '+880171111111', 'role' => 'Tenant Owner', 'department' => 'Management', 'designation' => 'Business Owner'],
                ['name' => 'Nadim Hasan', 'email' => 'manager@printflow.test', 'phone' => '+880172222222', 'role' => 'Manager', 'department' => 'Operations', 'designation' => 'Operations Manager'],
                ['name' => 'Jannat Akter', 'email' => 'sales@printflow.test', 'phone' => '+880173333333', 'role' => 'Sales / CRM', 'department' => 'Sales', 'designation' => 'CRM Executive'],
                ['name' => 'Morshed Alam', 'email' => 'inventory@printflow.test', 'phone' => '+880174444444', 'role' => 'Inventory Manager', 'department' => 'Store', 'designation' => 'Store Manager'],
                ['name' => 'Sadia Khan', 'email' => 'accounts@printflow.test', 'phone' => '+880175555555', 'role' => 'Accountant', 'department' => 'Accounts', 'designation' => 'Accountant'],
                ['name' => 'Sabbir Ahmed', 'email' => 'delivery@printflow.test', 'phone' => '+880176666666', 'role' => 'Delivery Staff', 'department' => 'Transport', 'designation' => 'Delivery Coordinator'],
            ];

            $createdUsers = collect();

            foreach ($users as $index => $data) {
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                    'status' => 'active',
                    'last_login_at' => now()->subHours($index),
                ]);

                $user->roles()->attach($roleMap[$data['role']]->id);
                $user->permissions()->sync($roleMap[$data['role']]->permissions->pluck('id'));

                Employee::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'employee_code' => 'EMP-10' . ($index + 1),
                    'department' => $data['department'],
                    'designation' => $data['designation'],
                    'joining_date' => now()->subMonths(12 + $index),
                    'salary' => 30000 + ($index * 5000),
                    'status' => 'active',
                ]);

                $createdUsers->push($user);
            }

            $categories = collect([
                ['name' => 'Commercial Print', 'slug' => 'commercial-print'],
                ['name' => 'Packaging', 'slug' => 'packaging'],
                ['name' => 'Label Print', 'slug' => 'label-print'],
                ['name' => 'Large Format', 'slug' => 'large-format'],
            ])->map(fn ($category) => ProductCategory::create([
                'tenant_id' => $tenant->id,
                'name' => $category['name'],
                'slug' => $category['slug'],
                'status' => 'active',
            ]))->keyBy('name');

            $products = collect([
                ['name' => 'Business Card', 'sku' => 'PRD-101', 'type' => 'service', 'category' => 'Commercial Print', 'base_price' => 18, 'unit' => 'box'],
                ['name' => 'Corporate Brochure', 'sku' => 'PRD-102', 'type' => 'service', 'category' => 'Commercial Print', 'base_price' => 0.80, 'unit' => 'piece'],
                ['name' => 'Packaging Box', 'sku' => 'PRD-103', 'type' => 'service', 'category' => 'Packaging', 'base_price' => 0.82, 'unit' => 'piece'],
                ['name' => 'Sticker Label Roll', 'sku' => 'PRD-104', 'type' => 'service', 'category' => 'Label Print', 'base_price' => 24, 'unit' => 'roll'],
                ['name' => 'Vinyl Banner', 'sku' => 'PRD-105', 'type' => 'service', 'category' => 'Large Format', 'base_price' => 2.40, 'unit' => 'sq ft'],
            ])->map(fn ($product) => Product::create([
                'tenant_id' => $tenant->id,
                'category_id' => $categories[$product['category']]->id,
                'name' => $product['name'],
                'sku' => $product['sku'],
                'type' => $product['type'],
                'base_price' => $product['base_price'],
                'unit' => $product['unit'],
                'status' => 'active',
            ]))->keyBy('name');

            $materialCategories = collect([
                ['name' => 'Paper'],
                ['name' => 'Ink'],
                ['name' => 'Board'],
                ['name' => 'Finishing'],
            ])->map(fn ($category) => RawMaterialCategory::create([
                'tenant_id' => $tenant->id,
                'name' => $category['name'],
            ]))->keyBy('name');

            $materials = collect([
                ['name' => 'Art Paper 300gsm', 'code' => 'MAT-201', 'category' => 'Paper', 'unit' => 'ream', 'current_stock' => 38, 'minimum_stock' => 20, 'average_cost' => 42],
                ['name' => 'Offset Ink Cyan', 'code' => 'MAT-202', 'category' => 'Ink', 'unit' => 'kg', 'current_stock' => 7, 'minimum_stock' => 10, 'average_cost' => 14],
                ['name' => 'Duplex Board', 'code' => 'MAT-203', 'category' => 'Board', 'unit' => 'sheet', 'current_stock' => 4800, 'minimum_stock' => 3500, 'average_cost' => 0.28],
                ['name' => 'Lamination Sheet', 'code' => 'MAT-204', 'category' => 'Finishing', 'unit' => 'roll', 'current_stock' => 6, 'minimum_stock' => 8, 'average_cost' => 22],
            ])->map(fn ($material) => RawMaterial::create([
                'tenant_id' => $tenant->id,
                'category_id' => $materialCategories[$material['category']]->id,
                'name' => $material['name'],
                'code' => $material['code'],
                'unit' => $material['unit'],
                'current_stock' => $material['current_stock'],
                'minimum_stock' => $material['minimum_stock'],
                'average_cost' => $material['average_cost'],
                'status' => 'active',
            ]))->keyBy('name');

            $warehouses = collect([
                ['name' => 'Main Store', 'code' => 'WH-01', 'address' => 'Tejgaon, Dhaka', 'manager_name' => 'Morshed Alam'],
                ['name' => 'Board Warehouse', 'code' => 'WH-02', 'address' => 'Gazipur', 'manager_name' => 'Morshed Alam'],
                ['name' => 'Dispatch Store', 'code' => 'WH-03', 'address' => 'Narayanganj', 'manager_name' => 'Sabbir Ahmed'],
            ])->map(fn ($warehouse) => Warehouse::create([
                'tenant_id' => $tenant->id,
                'name' => $warehouse['name'],
                'code' => $warehouse['code'],
                'address' => $warehouse['address'],
                'manager_name' => $warehouse['manager_name'],
                'status' => 'active',
            ]))->keyBy('name');

            foreach ($materials as $material) {
                foreach ($warehouses as $warehouse) {
                    InventoryStock::create([
                        'tenant_id' => $tenant->id,
                        'warehouse_id' => $warehouse->id,
                        'raw_material_id' => $material->id,
                        'quantity' => $warehouse->name === 'Main Store' ? $material->current_stock : round($material->current_stock / 2, 2),
                        'reserved_quantity' => $warehouse->name === 'Dispatch Store' ? 2 : 0,
                    ]);
                }
            }

            $customers = collect([
                ['customer_code' => 'CUS-001', 'company_name' => 'Nexa Retail Ltd.', 'contact_person' => 'Farzana Kabir', 'phone' => '+880174000001', 'email' => 'procurement@nexa.test', 'city' => 'Dhaka', 'status' => 'active'],
                ['customer_code' => 'CUS-002', 'company_name' => 'BluePeak Foods', 'contact_person' => 'Saif Mahmud', 'phone' => '+880174000002', 'email' => 'brand@bluepeak.test', 'city' => 'Chattogram', 'status' => 'vip'],
                ['customer_code' => 'CUS-003', 'company_name' => 'Prime Care Pharma', 'contact_person' => 'Rashed Khan', 'phone' => '+880174000003', 'email' => 'ops@primecare.test', 'city' => 'Dhaka', 'status' => 'active'],
                ['customer_code' => 'CUS-004', 'company_name' => 'Bright Kids School', 'contact_person' => 'Nadia Islam', 'phone' => '+880174000004', 'email' => 'admin@brightkids.test', 'city' => 'Sylhet', 'status' => 'lead'],
            ])->map(fn ($customer) => Customer::create([
                'tenant_id' => $tenant->id,
                'customer_code' => $customer['customer_code'],
                'company_name' => $customer['company_name'],
                'contact_person' => $customer['contact_person'],
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'billing_address' => $customer['city'] . ' Corporate Address',
                'delivery_address' => $customer['city'] . ' Delivery Point',
                'city' => $customer['city'],
                'status' => $customer['status'],
            ]))->keyBy('company_name');

            CustomerInteraction::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers['BluePeak Foods']->id,
                'user_id' => $createdUsers[2]->id,
                'type' => 'follow_up',
                'subject' => 'Label quotation follow-up',
                'notes' => 'Customer asked for matte finish option and revised delivery timeline.',
                'follow_up_at' => now()->addDay(),
            ]);

            $suppliers = collect([
                ['supplier_code' => 'SUP-011', 'company_name' => 'Paper Link BD', 'contact_person' => 'Mizanur', 'phone' => '+880181000011', 'email' => 'sales@paperlink.test', 'status' => 'active'],
                ['supplier_code' => 'SUP-012', 'company_name' => 'ColorChem Ink', 'contact_person' => 'Adiba', 'phone' => '+880181000012', 'email' => 'hello@colorchem.test', 'status' => 'preferred'],
                ['supplier_code' => 'SUP-013', 'company_name' => 'PackBoard House', 'contact_person' => 'Tanvir', 'phone' => '+880181000013', 'email' => 'orders@packboard.test', 'status' => 'active'],
                ['supplier_code' => 'SUP-014', 'company_name' => 'Plate Zone', 'contact_person' => 'Rezaul', 'phone' => '+880181000014', 'email' => 'contact@platezone.test', 'status' => 'active'],
            ])->map(fn ($supplier) => Supplier::create([
                'tenant_id' => $tenant->id,
                'supplier_code' => $supplier['supplier_code'],
                'company_name' => $supplier['company_name'],
                'contact_person' => $supplier['contact_person'],
                'phone' => $supplier['phone'],
                'email' => $supplier['email'],
                'address' => 'Supplier address for ' . $supplier['company_name'],
                'status' => $supplier['status'],
            ]))->keyBy('company_name');

            $purchaseOrders = collect([
                ['po_number' => 'PO-3001', 'supplier' => 'Paper Link BD', 'warehouse' => 'Main Store', 'total' => 1850, 'status' => 'ordered'],
                ['po_number' => 'PO-3002', 'supplier' => 'ColorChem Ink', 'warehouse' => 'Main Store', 'total' => 640, 'status' => 'partial_received'],
                ['po_number' => 'PO-3003', 'supplier' => 'PackBoard House', 'warehouse' => 'Board Warehouse', 'total' => 2400, 'status' => 'ordered'],
            ])->map(function ($po) use ($tenant, $suppliers, $warehouses, $createdUsers, $materials) {
                $purchaseOrder = PurchaseOrder::create([
                    'tenant_id' => $tenant->id,
                    'supplier_id' => $suppliers[$po['supplier']]->id,
                    'warehouse_id' => $warehouses[$po['warehouse']]->id,
                    'po_number' => $po['po_number'],
                    'order_date' => now()->subDays(4),
                    'expected_date' => now()->addDays(2),
                    'status' => $po['status'],
                    'subtotal' => $po['total'],
                    'total' => $po['total'],
                    'due_amount' => $po['total'],
                    'created_by' => $createdUsers[3]->id,
                ]);

                PurchaseOrderItem::create([
                    'tenant_id' => $tenant->id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'raw_material_id' => $materials->first()->id,
                    'quantity' => 20,
                    'received_quantity' => $po['status'] === 'partial_received' ? 10 : 0,
                    'unit_price' => 42,
                    'total_price' => 840,
                ]);

                return $purchaseOrder;
            });

            $quotations = collect([
                ['quote_number' => 'QUO-2601', 'customer' => 'BluePeak Foods', 'product' => 'Sticker Label Roll', 'total' => 1240, 'status' => 'sent'],
                ['quote_number' => 'QUO-2602', 'customer' => 'Nexa Retail Ltd.', 'product' => 'Packaging Box', 'total' => 3840, 'status' => 'approved'],
                ['quote_number' => 'QUO-2603', 'customer' => 'Bright Kids School', 'product' => 'Corporate Brochure', 'total' => 620, 'status' => 'draft'],
            ])->map(function ($quote) use ($tenant, $customers, $products, $createdUsers) {
                $quotation = Quotation::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customers[$quote['customer']]->id,
                    'quote_number' => $quote['quote_number'],
                    'inquiry_date' => now()->subDays(3),
                    'valid_until' => now()->addDays(5),
                    'status' => $quote['status'],
                    'subtotal' => $quote['total'],
                    'total' => $quote['total'],
                    'created_by' => $createdUsers[2]->id,
                    'approved_at' => $quote['status'] === 'approved' ? now()->subDay() : null,
                ]);

                QuotationItem::create([
                    'tenant_id' => $tenant->id,
                    'quotation_id' => $quotation->id,
                    'product_id' => $products[$quote['product']]->id,
                    'item_name' => $quote['product'],
                    'quantity' => 1000,
                    'unit_price' => round($quote['total'] / 1000, 2),
                    'total_price' => $quote['total'],
                    'specification_json' => ['size' => 'custom', 'print_type' => 'offset', 'finishing' => 'lamination'],
                ]);

                return $quotation;
            })->keyBy('quote_number');

            $orders = collect([
                ['order_number' => 'ORD-5001', 'customer' => 'Nexa Retail Ltd.', 'job_title' => 'Ramadan Packaging Box', 'product' => 'Packaging Box', 'total' => 1280, 'status' => 'printing'],
                ['order_number' => 'ORD-5002', 'customer' => 'Prime Care Pharma', 'job_title' => 'Medicine Carton Lot B', 'product' => 'Packaging Box', 'total' => 2460, 'status' => 'finishing'],
                ['order_number' => 'ORD-5003', 'customer' => 'BluePeak Foods', 'job_title' => 'Frozen Food Label', 'product' => 'Sticker Label Roll', 'total' => 860, 'status' => 'approval'],
                ['order_number' => 'ORD-5004', 'customer' => 'Bright Kids School', 'job_title' => 'Corporate Brochure', 'product' => 'Corporate Brochure', 'total' => 620, 'status' => 'pending'],
            ])->map(function ($order) use ($tenant, $customers, $products, $createdUsers, $quotations) {
                $createdOrder = Order::create([
                    'tenant_id' => $tenant->id,
                    'quotation_id' => $quotations->firstWhere('customer_id', $customers[$order['customer']]->id)?->id,
                    'customer_id' => $customers[$order['customer']]->id,
                    'order_number' => $order['order_number'],
                    'order_date' => now()->subDays(2),
                    'expected_delivery_date' => now()->addDays(3),
                    'priority' => 'normal',
                    'status' => $order['status'],
                    'job_title' => $order['job_title'],
                    'specifications_json' => ['material' => 'duplex board', 'print_type' => 'offset', 'color' => 'CMYK'],
                    'subtotal' => $order['total'],
                    'total' => $order['total'],
                    'due_amount' => $order['total'],
                    'assigned_manager_id' => $createdUsers[1]->id,
                    'created_by' => $createdUsers[2]->id,
                ]);

                OrderItem::create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $createdOrder->id,
                    'product_id' => $products[$order['product']]->id,
                    'item_name' => $order['product'],
                    'quantity' => 1000,
                    'unit_price' => round($order['total'] / 1000, 2),
                    'total_price' => $order['total'],
                    'specification_json' => ['finishing' => 'gloss lamination'],
                ]);

                foreach (['Pending', 'Design', 'Approval', 'Plate / Prepress', 'Printing', 'Cutting', 'Finishing', 'Packing', 'Ready Dispatch', 'Delivered'] as $sequence => $stageName) {
                    JobStage::create([
                        'tenant_id' => $tenant->id,
                        'order_id' => $createdOrder->id,
                        'stage_name' => $stageName,
                        'assigned_to' => $createdUsers[1]->id,
                        'status' => strtolower($stageName) === $order['status'] ? 'in_progress' : ($sequence < 2 ? 'completed' : 'pending'),
                        'sequence_no' => $sequence + 1,
                    ]);
                }

                return $createdOrder;
            })->keyBy('order_number');

            $invoiceOne = Invoice::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers['Nexa Retail Ltd.']->id,
                'order_id' => $orders['ORD-5001']->id,
                'invoice_number' => 'INV-9001',
                'invoice_date' => now()->subDays(1),
                'due_date' => now()->addDays(6),
                'status' => 'paid',
                'subtotal' => 1280,
                'total' => 1280,
                'paid_amount' => 1280,
                'due_amount' => 0,
                'created_by' => $createdUsers[4]->id,
            ]);

            Invoice::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers['Prime Care Pharma']->id,
                'order_id' => $orders['ORD-5002']->id,
                'invoice_number' => 'INV-9002',
                'invoice_date' => now(),
                'due_date' => now()->addDays(7),
                'status' => 'due',
                'subtotal' => 2460,
                'total' => 2460,
                'paid_amount' => 0,
                'due_amount' => 2460,
                'created_by' => $createdUsers[4]->id,
            ]);

            Payment::create([
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoiceOne->id,
                'payment_type' => 'customer_receipt',
                'party_type' => 'customer',
                'party_id' => $customers['Nexa Retail Ltd.']->id,
                'amount' => 1280,
                'payment_method' => 'bank_transfer',
                'reference_no' => 'TXN-1001',
                'payment_date' => now(),
                'created_by' => $createdUsers[4]->id,
            ]);

            foreach ([
                ['category' => 'Utility', 'title' => 'Electricity Bill', 'amount' => 860, 'reference_no' => 'EXP-101'],
                ['category' => 'Transport', 'title' => 'Courier and fuel', 'amount' => 340, 'reference_no' => 'EXP-102'],
                ['category' => 'Maintenance', 'title' => 'Cutter service', 'amount' => 420, 'reference_no' => 'EXP-103'],
                ['category' => 'Office', 'title' => 'Stationery', 'amount' => 85, 'reference_no' => 'EXP-104'],
            ] as $expense) {
                Expense::create([
                    'tenant_id' => $tenant->id,
                    'expense_date' => now(),
                    'category' => $expense['category'],
                    'title' => $expense['title'],
                    'amount' => $expense['amount'],
                    'reference_no' => $expense['reference_no'],
                    'created_by' => $createdUsers[4]->id,
                ]);
            }

            foreach ([
                ['delivery_number' => 'DLV-7001', 'order' => 'ORD-5001', 'status' => 'delivered', 'vehicle_no' => 'DHK-Metro-11'],
                ['delivery_number' => 'DLV-7002', 'order' => 'ORD-5002', 'status' => 'assigned', 'vehicle_no' => 'DHK-Metro-18'],
                ['delivery_number' => 'DLV-7003', 'order' => 'ORD-5003', 'status' => 'out_for_delivery', 'vehicle_no' => '3PL'],
            ] as $delivery) {
                Delivery::create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $orders[$delivery['order']]->id,
                    'delivery_number' => $delivery['delivery_number'],
                    'delivery_date' => now(),
                    'delivery_address' => 'Customer delivery location',
                    'assigned_to' => $createdUsers[5]->id,
                    'vehicle_no' => $delivery['vehicle_no'],
                    'transport_cost' => 32,
                    'status' => $delivery['status'],
                    'delivered_at' => $delivery['status'] === 'delivered' ? now() : null,
                    'received_by' => $delivery['status'] === 'delivered' ? 'Store In-charge' : null,
                ]);
            }

            Setting::create([
                'tenant_id' => $tenant->id,
                'key' => 'business_profile',
                'value_json' => [
                    'company_name' => $tenant->name,
                    'currency' => 'USD',
                    'timezone' => 'Asia/Dhaka',
                ],
            ]);

            Setting::create([
                'tenant_id' => $tenant->id,
                'key' => 'document_series',
                'value_json' => [
                    'quotation_prefix' => 'QUO',
                    'order_prefix' => 'ORD',
                    'invoice_prefix' => 'INV',
                ],
            ]);

            DB::table('subscriptions')->insert([
                'tenant_id' => $tenant->id,
                'plan_id' => $growth->id,
                'starts_at' => now()->subMonth(),
                'ends_at' => now()->addMonth(),
                'trial_ends_at' => now()->addDays(14),
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'amount' => 79,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $business->update(['status' => 'active']);
            $starter->update(['status' => 'active']);
        });
    }
}
