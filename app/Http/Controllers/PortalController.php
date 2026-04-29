<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InkType;
use App\Models\JobOrder;
use App\Models\Order as PrintOrder;
use App\Models\PaperType;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\RawMaterial;
use App\Models\Role;
use App\Models\Setting;
use App\Models\StandardSheet;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PortalController extends Controller
{
    public function home(Request $request): View
    {
        return $this->renderPage('dashboard', $request);
    }

    public function show(Request $request, string $page): View
    {
        abort_unless(array_key_exists($page, $this->pages()), 404);

        return $this->renderPage($page, $request);
    }

    public function updateCompanyProfile(Request $request): RedirectResponse
    {
        $tenant = Tenant::firstOrFail();

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'vat_no' => ['nullable', 'string', 'max:100'],
            'bin_no' => ['nullable', 'string', 'max:100'],
            'trade_license_no' => ['nullable', 'string', 'max:100'],
            'logo_url' => ['nullable', 'string', 'max:1000'],
            'signature_name' => ['nullable', 'string', 'max:255'],
            'signature_title' => ['nullable', 'string', 'max:255'],
            'quotation_footer_note' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'key' => 'company_profile',
            ],
            [
                'value_json' => $data,
            ]
        );

        $tenant->update([
            'name' => $data['company_name'],
            'email' => $data['email'] ?? $tenant->email,
            'phone' => $data['phone'] ?? $tenant->phone,
            'address' => $data['address'] ?? $tenant->address,
            'logo' => $data['logo_url'] ?? $tenant->logo,
        ]);

        return redirect()->route('portal.page', ['page' => 'settings'])
            ->with('success', 'Company profile updated successfully.');
    }

    public function companyProfile(): View
    {
        $tenant = Tenant::with('plan')->firstOrFail();
        $companyProfile = (array) optional(
            Setting::where('tenant_id', $tenant->id)->where('key', 'company_profile')->first()
        )->value_json;

        return view('company-profile', [
            'companyProfile' => [
                'company_name' => $companyProfile['company_name'] ?? $tenant->name,
                'tagline' => $companyProfile['tagline'] ?? null,
                'address' => $companyProfile['address'] ?? $tenant->address,
                'phone' => $companyProfile['phone'] ?? $tenant->phone,
                'email' => $companyProfile['email'] ?? $tenant->email,
                'website' => $companyProfile['website'] ?? null,
                'vat_no' => $companyProfile['vat_no'] ?? null,
                'bin_no' => $companyProfile['bin_no'] ?? null,
                'trade_license_no' => $companyProfile['trade_license_no'] ?? null,
                'logo_url' => $companyProfile['logo_url'] ?? $tenant->logo,
                'signature_name' => $companyProfile['signature_name'] ?? null,
                'signature_title' => $companyProfile['signature_title'] ?? null,
                'quotation_footer_note' => $companyProfile['quotation_footer_note'] ?? null,
            ],
        ]);
    }

    private function renderPage(string $page, ?Request $request = null): View
    {
        $tenant = Tenant::with('plan')->firstOrFail();
        $companyProfile = (array) optional(
            Setting::where('tenant_id', $tenant->id)->where('key', 'company_profile')->first()
        )->value_json;
        $companyName = $companyProfile['company_name'] ?? $tenant->name;
        $primaryUser = User::where('tenant_id', $tenant->id)->first();
        $pages = $this->pages();
        $pageData = array_merge($this->pageData($page, $tenant, $request), $this->pageActions($page));

        return view('portal', [
            'pages' => $pages,
            'currentPage' => $page,
            'pageMeta' => $pages[$page],
            'pageData' => $pageData,
            'workspace' => [
                'name' => $companyName,
                'company_name' => $companyName,
                'company_tagline' => $companyProfile['tagline'] ?? null,
                'company_logo' => $companyProfile['logo_url'] ?? $tenant->logo,
                'company_profile_url' => route('company-profile.edit'),
                'role' => 'Tenant Owner',
                'user' => $primaryUser?->name ?? 'Workspace User',
                'status' => $tenant->is_active ? 'Workspace Active' : 'Workspace Inactive',
            ],
        ]);
    }

    private function pages(): array
    {
        return [
            'dashboard' => ['label' => 'Dashboard', 'title' => 'Operations dashboard', 'icon' => 'home'],
            'customers' => ['label' => 'Customers', 'title' => 'CRM and customer history', 'icon' => 'users'],
            'suppliers' => ['label' => 'Suppliers', 'title' => 'Supplier network and payables', 'icon' => 'building'],
            'products' => ['label' => 'Products', 'title' => 'Products and service presets', 'icon' => 'box'],
            'raw-materials' => ['label' => 'Raw Materials', 'title' => 'Material catalog and reorder alerts', 'icon' => 'layers'],
            'warehouses' => ['label' => 'Warehouses', 'title' => 'Warehouse and stock visibility', 'icon' => 'grid'],
            'quotations' => ['label' => 'Quotations', 'title' => 'Estimate, approve, convert', 'icon' => 'quote'],
            'orders' => ['label' => 'Orders', 'title' => 'Jobs, schedules, and production', 'icon' => 'clipboard'],
            'purchases' => ['label' => 'Purchases', 'title' => 'POs, receiving, supplier payments', 'icon' => 'cart'],
            'invoices' => ['label' => 'Invoices', 'title' => 'Billing and collections', 'icon' => 'invoice'],
            'expenses' => ['label' => 'Expenses', 'title' => 'Operating cost control', 'icon' => 'wallet'],
            'deliveries' => ['label' => 'Deliveries', 'title' => 'Dispatch, routes, and proof of delivery', 'icon' => 'truck'],
            'reports' => ['label' => 'Reports', 'title' => 'Analytics, due reports, and profitability', 'icon' => 'chart'],
            'printing' => ['label' => 'Printing', 'title' => 'Sheet layout and orientation calculator', 'icon' => 'printer'],
            'users-roles' => ['label' => 'Users & Roles', 'title' => 'Tenant access and permissions', 'icon' => 'shield'],
            'settings' => ['label' => 'Settings', 'title' => 'Tenant configuration and business profile', 'icon' => 'settings'],
            'subscription' => ['label' => 'Subscription', 'title' => 'Plans, usage, and billing', 'icon' => 'spark'],
        ];
    }

    private function pageData(string $page, Tenant $tenant, ?Request $request = null): array
    {
        return match ($page) {
            'dashboard' => $this->dashboardData($tenant),
            'customers' => $this->customersData($tenant),
            'suppliers' => $this->suppliersData($tenant),
            'products' => $this->productsData($tenant),
            'raw-materials' => $this->rawMaterialsData($tenant),
            'warehouses' => $this->warehousesData($tenant),
            'quotations' => $this->quotationsData($tenant),
            'orders' => $this->ordersData($tenant),
            'purchases' => $this->purchasesData($tenant),
            'invoices' => $this->invoicesData($tenant),
            'expenses' => $this->expensesData($tenant),
            'deliveries' => $this->deliveriesData($tenant),
            'reports' => $this->reportsData($tenant, $request),
            'printing' => $this->printingData($request),
            'users-roles' => $this->usersRolesData($tenant),
            'settings' => $this->settingsData($tenant),
            'subscription' => $this->subscriptionData($tenant),
        };
    }

    private function pageActions(string $page): array
    {
        $createModules = ['customers', 'suppliers', 'products', 'raw-materials', 'warehouses', 'quotations', 'orders', 'purchases', 'invoices', 'expenses', 'deliveries'];

        return [
            'primary_action' => in_array($page, $createModules, true)
                ? ['label' => $this->primaryActionLabel($page), 'url' => route('modules.create', $page)]
                : match ($page) {
                    'dashboard' => ['label' => 'Create Quotation', 'url' => route('modules.create', 'quotations')],
                    'reports' => ['label' => 'Open Orders', 'url' => route('portal.page', 'orders')],
                    'printing' => ['label' => 'Open Settings', 'url' => route('portal.page', 'settings')],
                    'users-roles' => ['label' => 'Add User', 'url' => route('modules.create', 'users')],
                    'settings' => ['label' => 'Add Paper Type', 'url' => route('modules.create', 'paper-types')],
                    'subscription' => ['label' => 'View Reports', 'url' => route('portal.page', 'reports')],
                    default => null,
                },
            'secondary_action' => match ($page) {
                'dashboard' => ['label' => 'View Reports', 'url' => route('portal.page', 'reports')],
                'reports' => ['label' => 'Export Reports', 'url' => route('modules.export', 'invoices')],
                'printing' => null,
                'users-roles' => ['label' => 'Add Role', 'url' => route('modules.create', 'roles')],
                'settings' => ['label' => 'Add Ink Type', 'url' => route('modules.create', 'ink-types')],
                'subscription' => ['label' => 'Back to Dashboard', 'url' => route('portal.home')],
                default => ['label' => 'Export ' . str($page)->headline(), 'url' => route('modules.export', $page)],
            },
            'export_url' => match ($page) {
                'printing' => null,
                'dashboard' => route('modules.export', 'orders'),
                'users-roles' => route('modules.export', 'customers'),
                'settings' => route('modules.export', 'warehouses'),
                'subscription' => route('modules.export', 'invoices'),
                'reports' => route('modules.export', 'invoices'),
                default => route('modules.export', $page),
            },
        ];
    }

    private function printingData(?Request $request = null): array
    {
        $printWidth = max((float) ($request?->query('print_width', 8.0) ?? 8.0), 0);
        $printHeight = max((float) ($request?->query('print_height', 5.0) ?? 5.0), 0);
        $sheetWidth = max((float) ($request?->query('sheet_width', 20.0) ?? 20.0), 0);
        $sheetHeight = max((float) ($request?->query('sheet_height', 30.0) ?? 30.0), 0);

        $verticalColumns = $printWidth > 0 ? (int) floor($sheetWidth / $printWidth) : 0;
        $verticalRows = $printHeight > 0 ? (int) floor($sheetHeight / $printHeight) : 0;
        $verticalTotal = $verticalColumns * $verticalRows;

        $horizontalColumns = $printHeight > 0 ? (int) floor($sheetWidth / $printHeight) : 0;
        $horizontalRows = $printWidth > 0 ? (int) floor($sheetHeight / $printWidth) : 0;
        $horizontalTotal = $horizontalColumns * $horizontalRows;
        $sheetArea = $sheetWidth * $sheetHeight;
        $verticalUsedArea = $verticalTotal * ($printWidth * $printHeight);
        $horizontalUsedArea = $horizontalTotal * ($printWidth * $printHeight);
        $verticalWastageArea = max($sheetArea - $verticalUsedArea, 0);
        $horizontalWastageArea = max($sheetArea - $horizontalUsedArea, 0);
        $verticalWastagePercent = $sheetArea > 0 ? ($verticalWastageArea / $sheetArea) * 100 : 0;
        $horizontalWastagePercent = $sheetArea > 0 ? ($horizontalWastageArea / $sheetArea) * 100 : 0;

        return [
            'eyebrow' => 'Print Planning',
            'headline' => 'Calculate how many print pages fit on one main sheet in vertical and horizontal orientation.',
            'description' => 'Enter page and sheet size, then compare both orientations with a visual layout.',
            'actions' => ['Calculate Layout', 'Compare Orientation'],
            'stats' => [
                ['label' => 'Vertical Fit', 'value' => (string) $verticalTotal, 'note' => $verticalColumns . ' columns × ' . $verticalRows . ' rows'],
                ['label' => 'Horizontal Fit', 'value' => (string) $horizontalTotal, 'note' => $horizontalColumns . ' columns × ' . $horizontalRows . ' rows'],
                ['label' => 'Best Orientation', 'value' => $verticalTotal >= $horizontalTotal ? 'Vertical' : 'Horizontal', 'note' => 'higher output from same sheet'],
                ['label' => 'Sheet Size', 'value' => rtrim(rtrim(number_format($sheetWidth, 2, '.', ''), '0'), '.') . ' × ' . rtrim(rtrim(number_format($sheetHeight, 2, '.', ''), '0'), '.'), 'note' => 'input main sheet dimensions'],
            ],
            'printing_calculator' => [
                'inputs' => [
                    'print_width' => $printWidth,
                    'print_height' => $printHeight,
                    'sheet_width' => $sheetWidth,
                    'sheet_height' => $sheetHeight,
                ],
                'vertical' => [
                    'columns' => $verticalColumns,
                    'rows' => $verticalRows,
                    'total' => $verticalTotal,
                    'cell_width' => $printWidth,
                    'cell_height' => $printHeight,
                    'wastage_area' => round($verticalWastageArea, 2),
                    'wastage_percent' => round($verticalWastagePercent, 2),
                ],
                'horizontal' => [
                    'columns' => $horizontalColumns,
                    'rows' => $horizontalRows,
                    'total' => $horizontalTotal,
                    'cell_width' => $printHeight,
                    'cell_height' => $printWidth,
                    'wastage_area' => round($horizontalWastageArea, 2),
                    'wastage_percent' => round($horizontalWastagePercent, 2),
                ],
            ],
            'table' => [
                'title' => 'Orientation summary',
                'columns' => ['Orientation', 'Columns', 'Rows', 'Total', 'Wastage Area', 'Wastage %'],
                'rows' => [
                    ['Vertical', (string) $verticalColumns, (string) $verticalRows, (string) $verticalTotal, (string) round($verticalWastageArea, 2), round($verticalWastagePercent, 2) . '%'],
                    ['Horizontal', (string) $horizontalColumns, (string) $horizontalRows, (string) $horizontalTotal, (string) round($horizontalWastageArea, 2), round($horizontalWastagePercent, 2) . '%'],
                ],
            ],
            'side_panel' => [
                'title' => 'How it works',
                'items' => [
                    'Vertical: page width × page height',
                    'Horizontal: page height × page width',
                    'Columns = floor(sheet width / page width)',
                    'Rows = floor(sheet height / page height)',
                ],
            ],
        ];
    }

    private function primaryActionLabel(string $page): string
    {
        return match ($page) {
            'customers' => 'Add Customer',
            'suppliers' => 'Add Supplier',
            'products' => 'Add Product',
            'raw-materials' => 'Add Material',
            'warehouses' => 'Add Warehouse',
            'quotations' => 'New Quotation',
            'orders' => 'Create Order',
            'purchases' => 'Create PO',
            'invoices' => 'New Invoice',
            'expenses' => 'Add Expense',
            'deliveries' => 'Create Dispatch',
            default => 'Create',
        };
    }

    private function dashboardData(Tenant $tenant): array
    {
        $orders = PrintOrder::with('customer')->where('tenant_id', $tenant->id)->latest()->take(6)->get();

        return [
            'eyebrow' => 'Printing Press SaaS',
            'headline' => 'Manage quotation, orders, production, inventory, finance, and delivery from one workspace.',
            'description' => 'Live metrics are now coming from the ERP database instead of hardcoded template values.',
            'actions' => ['Create Quotation', 'View Reports'],
            'stats' => [
                ['label' => 'Total Orders', 'value' => (string) PrintOrder::where('tenant_id', $tenant->id)->count(), 'note' => 'jobs tracked in ERP'],
                ['label' => 'Active Customers', 'value' => (string) Customer::where('tenant_id', $tenant->id)->count(), 'note' => 'CRM records in workspace'],
                ['label' => 'Pending Invoices', 'value' => '$' . number_format((float) Invoice::where('tenant_id', $tenant->id)->sum('due_amount'), 0), 'note' => 'current receivable balance'],
                ['label' => 'Low Stock Alerts', 'value' => (string) RawMaterial::where('tenant_id', $tenant->id)->whereColumn('current_stock', '<=', 'minimum_stock')->count(), 'note' => 'materials below threshold'],
            ],
            'feature_cards' => [
                ['title' => 'Multi-tenant ready', 'text' => 'Tenant, users, plans, roles, and settings all exist in the database and are wired into the ERP.'],
                ['title' => 'Production workflow', 'text' => 'Orders now have stage records in the database for pending, approval, printing, finishing, and dispatch flow.'],
                ['title' => 'Finance visibility', 'text' => 'Invoices, payments, expenses, and supplier-linked purchases are seeded and queryable.'],
            ],
            'table' => [
                'title' => 'Recent orders',
                'columns' => ['Order', 'Customer', 'Job Title', 'Delivery Date', 'Amount', 'Status'],
                'rows' => $orders->map(fn (PrintOrder $order) => [
                    $order->order_number,
                    $order->customer?->company_name ?? '-',
                    $order->job_title,
                    optional($order->expected_delivery_date)->format('M d, Y'),
                    '$' . number_format((float) $order->total, 0),
                    str($order->status)->headline()->toString(),
                ])->all(),
            ],
            'side_panel' => [
                'title' => 'MVP modules',
                'items' => ['Customers', 'Suppliers', 'Products', 'Raw Materials', 'Warehouses', 'Quotations', 'Orders', 'Purchases', 'Invoices', 'Deliveries'],
            ],
        ];
    }

    private function customersData(Tenant $tenant): array
    {
        $customers = Customer::where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'CRM Module',
            'headline' => 'Build customer records, inquiry tracking, follow-ups, notes, and order history.',
            'description' => 'Customer listings and KPI cards are pulled directly from the customers and customer_interactions tables.',
            'actions' => ['Add Customer', 'Export CRM'],
            'stats' => [
                ['label' => 'Total Customers', 'value' => (string) $customers->count(), 'note' => 'all customer records'],
                ['label' => 'Active Customers', 'value' => (string) $customers->where('status', 'active')->count(), 'note' => 'currently active accounts'],
                ['label' => 'VIP Customers', 'value' => (string) $customers->where('status', 'vip')->count(), 'note' => 'priority service clients'],
                ['label' => 'Lead Records', 'value' => (string) $customers->where('status', 'lead')->count(), 'note' => 'not yet fully converted'],
            ],
            'table' => [
                'title' => 'Customer directory',
                'columns' => ['Code', 'Company', 'Contact Person', 'Phone', 'City', 'Status'],
                'rows' => $customers->map(fn (Customer $customer) => [
                    $customer->customer_code,
                    $customer->company_name,
                    $customer->contact_person,
                    $customer->phone,
                    $customer->city,
                    str($customer->status)->headline()->toString(),
                ])->all(),
                'record_ids' => $customers->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'CRM actions',
                'items' => ['Customer notes', 'Inquiry history', 'Follow-up schedule', 'Quotation conversion', 'Order history'],
            ],
        ];
    }

    private function suppliersData(Tenant $tenant): array
    {
        $suppliers = Supplier::where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Supplier Management',
            'headline' => 'Control supplier records, payable tracking, purchase history, and source materials.',
            'description' => 'Supplier data is connected to purchase orders and can be extended into full payable workflows.',
            'actions' => ['Add Supplier', 'Create PO'],
            'stats' => [
                ['label' => 'Suppliers', 'value' => (string) $suppliers->count(), 'note' => 'registered suppliers'],
                ['label' => 'Preferred', 'value' => (string) $suppliers->where('status', 'preferred')->count(), 'note' => 'top-ranked vendors'],
                ['label' => 'Open POs', 'value' => (string) PurchaseOrder::where('tenant_id', $tenant->id)->whereIn('status', ['draft', 'ordered', 'partial_received'])->count(), 'note' => 'awaiting full closure'],
                ['label' => 'Supplier Due', 'value' => '$' . number_format((float) PurchaseOrder::where('tenant_id', $tenant->id)->sum('due_amount'), 0), 'note' => 'payables total'],
            ],
            'table' => [
                'title' => 'Supplier directory',
                'columns' => ['Code', 'Company', 'Contact', 'Email', 'Phone', 'Status'],
                'rows' => $suppliers->map(fn (Supplier $supplier) => [
                    $supplier->supplier_code,
                    $supplier->company_name,
                    $supplier->contact_person,
                    $supplier->email,
                    $supplier->phone,
                    str($supplier->status)->headline()->toString(),
                ])->all(),
                'record_ids' => $suppliers->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'Key workflows',
                'items' => ['Supplier onboarding', 'Payable tracking', 'Material linkage', 'Purchase receiving', 'Supplier performance'],
            ],
        ];
    }

    private function productsData(Tenant $tenant): array
    {
        $products = Product::with('category')->where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Products & Services',
            'headline' => 'Define print products, categories, base prices, units, and specification presets.',
            'description' => 'Products are database-backed and connected to categories so quotations and orders can reference them cleanly.',
            'actions' => ['Add Product', 'Manage Categories'],
            'stats' => [
                ['label' => 'Products', 'value' => (string) $products->count(), 'note' => 'service and item definitions'],
                ['label' => 'Categories', 'value' => (string) $products->pluck('category_id')->filter()->unique()->count(), 'note' => 'active product groups'],
                ['label' => 'Average Price', 'value' => '$' . number_format((float) $products->avg('base_price'), 2), 'note' => 'mean base price'],
                ['label' => 'Active Products', 'value' => (string) $products->where('status', 'active')->count(), 'note' => 'currently sellable'],
            ],
            'table' => [
                'title' => 'Product catalog',
                'columns' => ['SKU', 'Name', 'Category', 'Unit', 'Base Price', 'Status'],
                'rows' => $products->map(fn (Product $product) => [
                    $product->sku,
                    $product->name,
                    $product->category?->name ?? '-',
                    $product->unit,
                    '$' . number_format((float) $product->base_price, 2),
                    str($product->status)->headline()->toString(),
                ])->all(),
                'record_ids' => $products->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'Preset examples',
                'items' => ['Business card', 'Brochure', 'Packaging box', 'Sticker label', 'Vinyl banner', 'Custom job'],
            ],
        ];
    }

    private function rawMaterialsData(Tenant $tenant): array
    {
        $materials = RawMaterial::where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Inventory Control',
            'headline' => 'Track raw materials, stock levels, minimum thresholds, and average cost.',
            'description' => 'Material counts, low-stock alerts, and stock values are coming from raw_materials and inventory_stocks.',
            'actions' => ['Add Material', 'Stock Adjustment'],
            'stats' => [
                ['label' => 'Materials', 'value' => (string) $materials->count(), 'note' => 'catalogued raw materials'],
                ['label' => 'Low Stock', 'value' => (string) $materials->filter(fn (RawMaterial $material) => $material->current_stock <= $material->minimum_stock)->count(), 'note' => 'needs reorder action'],
                ['label' => 'Stock Value', 'value' => '$' . number_format((float) $materials->sum(fn (RawMaterial $material) => $material->current_stock * $material->average_cost), 0), 'note' => 'estimated inventory value'],
                ['label' => 'Avg Cost', 'value' => '$' . number_format((float) $materials->avg('average_cost'), 2), 'note' => 'average material cost'],
            ],
            'table' => [
                'title' => 'Material catalog',
                'columns' => ['Code', 'Material', 'Unit', 'Current Stock', 'Minimum Stock', 'Status'],
                'rows' => $materials->map(fn (RawMaterial $material) => [
                    $material->code,
                    $material->name,
                    $material->unit,
                    number_format((float) $material->current_stock, 2),
                    number_format((float) $material->minimum_stock, 2),
                    $material->current_stock <= $material->minimum_stock ? 'Low' : 'Healthy',
                ])->all(),
                'record_ids' => $materials->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'Material examples',
                'items' => ['Paper', 'Ink', 'Board', 'Glue', 'Plate', 'Finishing items'],
            ],
        ];
    }

    private function warehousesData(Tenant $tenant): array
    {
        $warehouses = Warehouse::with('stocks')->where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Warehouse Module',
            'headline' => 'Monitor warehouse locations, manager assignments, stock positions, and reserved quantities.',
            'description' => 'Warehouse records, stock counts, and reserved quantities are pulled from the inventory tables.',
            'actions' => ['Add Warehouse', 'View Transactions'],
            'stats' => [
                ['label' => 'Warehouses', 'value' => (string) $warehouses->count(), 'note' => 'active storage locations'],
                ['label' => 'Reserved Qty', 'value' => number_format((float) $warehouses->flatMap->stocks->sum('reserved_quantity'), 2), 'note' => 'material reserved for jobs'],
                ['label' => 'Stock Lines', 'value' => (string) $warehouses->flatMap->stocks->count(), 'note' => 'warehouse stock rows'],
                ['label' => 'Active Sites', 'value' => (string) $warehouses->where('status', 'active')->count(), 'note' => 'usable warehouse locations'],
            ],
            'table' => [
                'title' => 'Warehouse directory',
                'columns' => ['Code', 'Name', 'Manager', 'Address', 'Stock Lines', 'Status'],
                'rows' => $warehouses->map(fn (Warehouse $warehouse) => [
                    $warehouse->code,
                    $warehouse->name,
                    $warehouse->manager_name,
                    $warehouse->address,
                    (string) $warehouse->stocks->count(),
                    str($warehouse->status)->headline()->toString(),
                ])->all(),
                'record_ids' => $warehouses->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'Warehouse actions',
                'items' => ['Stock in/out', 'Reserve stock', 'Release stock', 'Adjustments', 'Transfer planning'],
            ],
        ];
    }

    private function quotationsData(Tenant $tenant): array
    {
        $quotations = Quotation::with('customer')->where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Quotation Management',
            'headline' => 'Create costed quotations with print specs, tax, discount, approvals, and conversion to order.',
            'description' => 'Quotation records are now stored in quotations and quotation_items and linked to customers.',
            'actions' => ['New Quotation', 'Convert Approved'],
            'stats' => [
                ['label' => 'Open Quotes', 'value' => (string) $quotations->whereIn('status', ['draft', 'sent'])->count(), 'note' => 'awaiting next action'],
                ['label' => 'Approved', 'value' => (string) $quotations->where('status', 'approved')->count(), 'note' => 'ready for conversion'],
                ['label' => 'Converted', 'value' => (string) $quotations->where('status', 'converted')->count(), 'note' => 'converted into orders'],
                ['label' => 'Quote Value', 'value' => '$' . number_format((float) $quotations->sum('total'), 0), 'note' => 'total quotation pipeline'],
            ],
            'table' => [
                'title' => 'Quotation list',
                'columns' => ['Quote No', 'Customer', 'Inquiry Date', 'Valid Until', 'Total', 'Status'],
                'rows' => $quotations->map(fn (Quotation $quotation) => [
                    $quotation->quote_number,
                    $quotation->customer?->company_name ?? '-',
                    optional($quotation->inquiry_date)->format('M d, Y'),
                    optional($quotation->valid_until)->format('M d, Y'),
                    '$' . number_format((float) $quotation->total, 0),
                    str($quotation->status)->headline()->toString(),
                ])->all(),
                'record_ids' => $quotations->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'Quote tools',
                'items' => ['Print specs', 'Costing', 'Discount and tax', 'Approval flow', 'Convert to order'],
            ],
        ];
    }

    private function ordersData(Tenant $tenant): array
    {
        $orders = JobOrder::with(['customer', 'payments'])->where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Order Management',
            'headline' => 'Turn approved quotes into tracked print jobs with statuses, managers, and expected delivery.',
            'description' => 'Orders now use the printing-specific job order engine (GSM, paper specs, calculations, advance gate, and production statuses).',
            'actions' => ['Create Order', 'Assign Manager'],
            'stats' => [
                ['label' => 'Open Jobs', 'value' => (string) $orders->count(), 'note' => 'total orders in workspace'],
                ['label' => 'In Production', 'value' => (string) $orders->where('status', 'in_production')->count(), 'note' => 'currently on production floor'],
                ['label' => 'Due This Week', 'value' => (string) $orders->filter(fn (JobOrder $order) => optional($order->due_date)?->isCurrentWeek())->count(), 'note' => 'delivery horizon'],
                ['label' => 'Receivable', 'value' => '$' . number_format((float) $orders->sum('estimated_total_price'), 0), 'note' => 'estimated job receivable'],
            ],
            'table' => [
                'title' => 'Order board',
                'columns' => ['Order No', 'Customer', 'Job Title', 'Order Date', 'Expected Delivery', 'Invoice Status', 'Status'],
                'rows' => $orders->map(function (JobOrder $order): array {
                    $totalAmount = (float) $order->estimated_total_price;
                    $paidAmount = (float) $order->payments->sum('amount');

                    if ($paidAmount <= 0.0001) {
                        $invoiceStatus = 'Due';
                    } elseif ($paidAmount + 0.0001 >= $totalAmount && $totalAmount > 0) {
                        $invoiceStatus = 'Paid';
                    } else {
                        $invoiceStatus = 'Partial';
                    }

                    return [
                        $order->job_number,
                        $order->customer?->company_name ?? '-',
                        $order->job_title,
                        optional($order->order_date)->format('M d, Y'),
                        optional($order->due_date)->format('M d, Y'),
                        $invoiceStatus,
                        str($order->status)->headline()->toString(),
                    ];
                })->all(),
                'record_ids' => $orders->pluck('id')->all(),
                'status_values' => $orders->pluck('status')->all(),
                'status_options' => ['draft', 'confirmed', 'in_production', 'quality_check', 'delivered'],
            ],
            'side_panel' => [
                'title' => 'Production stages',
                'items' => ['Pending', 'Design', 'Approval', 'Plate / Prepress', 'Printing', 'Cutting', 'Finishing', 'Packing', 'Ready Dispatch', 'Delivered'],
            ],
        ];
    }

    private function purchasesData(Tenant $tenant): array
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'warehouse'])->where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Purchase Management',
            'headline' => 'Manage purchase orders, receiving, stock updates, and supplier payment tracking.',
            'description' => 'Purchases are linked to suppliers, warehouses, material items, and payable balances.',
            'actions' => ['Create PO', 'Receive Goods'],
            'stats' => [
                ['label' => 'Open POs', 'value' => (string) $purchaseOrders->whereIn('status', ['draft', 'ordered', 'partial_received'])->count(), 'note' => 'orders not fully closed'],
                ['label' => 'Received', 'value' => (string) $purchaseOrders->where('status', 'received')->count(), 'note' => 'fully received POs'],
                ['label' => 'Committed Value', 'value' => '$' . number_format((float) $purchaseOrders->sum('total'), 0), 'note' => 'gross purchase value'],
                ['label' => 'Due Amount', 'value' => '$' . number_format((float) $purchaseOrders->sum('due_amount'), 0), 'note' => 'supplier payable total'],
            ],
            'table' => [
                'title' => 'Purchase order list',
                'columns' => ['PO No', 'Supplier', 'Warehouse', 'Expected Date', 'Total', 'Status'],
                'rows' => $purchaseOrders->map(fn (PurchaseOrder $purchaseOrder) => [
                    $purchaseOrder->po_number,
                    $purchaseOrder->supplier?->company_name ?? '-',
                    $purchaseOrder->warehouse?->name ?? '-',
                    optional($purchaseOrder->expected_date)->format('M d, Y'),
                    '$' . number_format((float) $purchaseOrder->total, 0),
                    str($purchaseOrder->status)->headline()->toString(),
                ])->all(),
                'record_ids' => $purchaseOrders->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'PO flow',
                'items' => ['Draft', 'Ordered', 'Partial received', 'Received', 'Cancelled'],
            ],
        ];
    }

    private function invoicesData(Tenant $tenant): array
    {
        $invoices = Invoice::with('customer')->where('tenant_id', $tenant->id)->latest()->get();
        $paidInvoices = $invoices->filter(function (Invoice $invoice): bool {
            $total = (float) $invoice->total;
            $paid = (float) $invoice->paid_amount;
            $due = (float) $invoice->due_amount;

            return $total > 0 && ($paid + 0.0001 >= $total || $due <= 0.0001);
        })->count();

        return [
            'eyebrow' => 'Financial Management',
            'headline' => 'Issue invoices, track due dates, log payments, and monitor collection performance.',
            'description' => 'Invoice totals and due balances are now read from the invoices and payments tables.',
            'actions' => ['New Invoice', 'Record Payment'],
            'stats' => [
                ['label' => 'Invoices Issued', 'value' => (string) $invoices->count(), 'note' => 'all generated invoices'],
                ['label' => 'Collected', 'value' => '$' . number_format((float) $invoices->sum('paid_amount'), 0), 'note' => 'payments received'],
                ['label' => 'Due Amount', 'value' => '$' . number_format((float) $invoices->sum('due_amount'), 0), 'note' => 'outstanding balance'],
                ['label' => 'Paid Invoices', 'value' => (string) $paidInvoices, 'note' => 'closed billing documents'],
            ],
            'table' => [
                'title' => 'Invoice register',
                'columns' => ['Invoice No', 'Customer', 'Invoice Date', 'Due Date', 'Total', 'Payment Status'],
                'rows' => $invoices->map(function (Invoice $invoice): array {
                    $total = (float) $invoice->total;
                    $paid = (float) $invoice->paid_amount;
                    $due = (float) $invoice->due_amount;

                    if ($paid <= 0.0001 && $due > 0.0001) {
                        $paymentStatus = 'Due';
                    } elseif ($total > 0 && ($paid + 0.0001 >= $total || $due <= 0.0001)) {
                        $paymentStatus = 'Paid';
                    } else {
                        $paymentStatus = 'Partial';
                    }

                    return [
                        $invoice->invoice_number,
                        $invoice->customer?->company_name ?? '-',
                        optional($invoice->invoice_date)->format('M d, Y'),
                        optional($invoice->due_date)->format('M d, Y'),
                        '$' . number_format((float) $invoice->total, 0),
                        $paymentStatus,
                    ];
                })->all(),
                'record_ids' => $invoices->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'Finance tools',
                'items' => ['Invoice issue', 'Payment record', 'Due report', 'Cash ledger', 'Order-wise profit'],
            ],
        ];
    }

    private function expensesData(Tenant $tenant): array
    {
        $expenses = Expense::where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Expense Tracking',
            'headline' => 'Track operating expenses, categories, reference numbers, and approval notes.',
            'description' => 'Expenses are stored and aggregated from the expenses table for accounting visibility.',
            'actions' => ['Add Expense', 'Export Ledger'],
            'stats' => [
                ['label' => 'Expenses', 'value' => (string) $expenses->count(), 'note' => 'expense entries recorded'],
                ['label' => 'Total Spend', 'value' => '$' . number_format((float) $expenses->sum('amount'), 0), 'note' => 'current expense total'],
                ['label' => 'Utilities', 'value' => '$' . number_format((float) $expenses->where('category', 'Utility')->sum('amount'), 0), 'note' => 'utility costs'],
                ['label' => 'Transport', 'value' => '$' . number_format((float) $expenses->where('category', 'Transport')->sum('amount'), 0), 'note' => 'dispatch-related spend'],
            ],
            'table' => [
                'title' => 'Expense ledger',
                'columns' => ['Date', 'Category', 'Title', 'Reference', 'Amount', 'Status'],
                'rows' => $expenses->map(fn (Expense $expense) => [
                    optional($expense->expense_date)->format('M d, Y'),
                    $expense->category,
                    $expense->title,
                    $expense->reference_no,
                    '$' . number_format((float) $expense->amount, 0),
                    'Recorded',
                ])->all(),
                'record_ids' => $expenses->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'Expense categories',
                'items' => ['Utility', 'Transport', 'Maintenance', 'Salary support', 'Office', 'Miscellaneous'],
            ],
        ];
    }

    private function deliveriesData(Tenant $tenant): array
    {
        $deliveries = Delivery::with('jobOrder')->where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Transport & Dispatch',
            'headline' => 'Assign deliveries, monitor route notes, transport cost, POD, and delivery status.',
            'description' => 'Dispatch data is coming from deliveries tied to ERP orders and assigned tenant users.',
            'actions' => ['Create Dispatch', 'Assign Driver'],
            'stats' => [
                ['label' => 'Deliveries', 'value' => (string) $deliveries->count(), 'note' => 'all dispatch records'],
                ['label' => 'Delivered', 'value' => (string) $deliveries->where('status', 'delivered')->count(), 'note' => 'completed dispatches'],
                ['label' => 'Out for Delivery', 'value' => (string) $deliveries->where('status', 'out_for_delivery')->count(), 'note' => 'live route movement'],
                ['label' => 'Transport Cost', 'value' => '$' . number_format((float) $deliveries->sum('transport_cost'), 0), 'note' => 'dispatch cost total'],
            ],
            'table' => [
                'title' => 'Delivery list',
                'columns' => ['Delivery No', 'Order', 'Delivery Date', 'Vehicle', 'Transport Cost', 'Status'],
                'rows' => $deliveries->map(fn (Delivery $delivery) => [
                    $delivery->delivery_number,
                    $delivery->jobOrder?->job_number ?? ('ID: ' . $delivery->order_id),
                    optional($delivery->delivery_date)->format('M d, Y'),
                    $delivery->vehicle_no,
                    '$' . number_format((float) $delivery->transport_cost, 0),
                    str($delivery->status)->headline()->toString(),
                ])->all(),
                'record_ids' => $deliveries->pluck('id')->all(),
            ],
            'side_panel' => [
                'title' => 'Delivery statuses',
                'items' => ['Pending', 'Assigned', 'Out for delivery', 'Delivered', 'Failed', 'Returned'],
            ],
        ];
    }

    private function reportsData(Tenant $tenant, ?Request $request = null): array
    {
        $period = $request?->query('period', 'monthly');
        if (! in_array($period, ['monthly', 'day'], true)) {
            $period = 'monthly';
        }

        $selectedMonth = (string) ($request?->query('month') ?: now()->format('Y-m'));
        try {
            $monthCursor = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Throwable) {
            $monthCursor = now()->startOfMonth();
            $selectedMonth = $monthCursor->format('Y-m');
        }

        $invoiceBase = Invoice::where('tenant_id', $tenant->id)->whereNotNull('invoice_date');
        $expenseBase = Expense::where('tenant_id', $tenant->id)->whereNotNull('expense_date');

        if ($period === 'day') {
            $start = $monthCursor->copy()->startOfMonth();
            $end = $monthCursor->copy()->endOfMonth();
            $labels = collect(range(1, (int) $start->daysInMonth))
                ->map(fn (int $day) => str_pad((string) $day, 2, '0', STR_PAD_LEFT))
                ->values();

            $invoiceRows = (clone $invoiceBase)
                ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
                ->with('customer:id,company_name')
                ->get(['customer_id', 'invoice_date', 'total', 'due_amount']);
            $expenseRows = (clone $expenseBase)
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->get(['expense_date', 'amount']);

            $invoiceByKey = $invoiceRows
                ->groupBy(fn (Invoice $invoice) => optional($invoice->invoice_date)->format('d'))
                ->map(fn ($group) => (float) $group->sum('total'));
            $expenseByKey = $expenseRows
                ->groupBy(fn (Expense $expense) => optional($expense->expense_date)->format('d'))
                ->map(fn ($group) => (float) $group->sum('amount'));
        } else {
            $year = (int) now()->year;
            $labels = collect(range(1, 12))
                ->map(fn (int $month) => Carbon::create($year, $month, 1)->format('M'))
                ->values();

            $invoiceRows = (clone $invoiceBase)
                ->whereYear('invoice_date', $year)
                ->with('customer:id,company_name')
                ->get(['customer_id', 'invoice_date', 'total', 'due_amount']);
            $expenseRows = (clone $expenseBase)
                ->whereYear('expense_date', $year)
                ->get(['expense_date', 'amount']);

            $invoiceByKey = $invoiceRows
                ->groupBy(fn (Invoice $invoice) => optional($invoice->invoice_date)->format('m'))
                ->map(fn ($group) => (float) $group->sum('total'));
            $expenseByKey = $expenseRows
                ->groupBy(fn (Expense $expense) => optional($expense->expense_date)->format('m'))
                ->map(fn ($group) => (float) $group->sum('amount'));
        }

        $revenueSeries = $labels->values()->map(function (string $label, int $index) use ($invoiceByKey, $period): float {
            $key = $period === 'day' ? $label : str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            return (float) ($invoiceByKey[$key] ?? 0);
        })->values();

        $expenseSeries = $labels->values()->map(function (string $label, int $index) use ($expenseByKey, $period): float {
            $key = $period === 'day' ? $label : str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            return (float) ($expenseByKey[$key] ?? 0);
        })->values();

        $profitSeries = $revenueSeries->zip($expenseSeries)->map(
            fn ($pair): float => (float) $pair[0] - (float) $pair[1]
        )->values();

        $sales = (float) $revenueSeries->sum();
        $expenses = (float) $expenseSeries->sum();

        $customerRows = $invoiceRows
            ->groupBy('customer_id')
            ->map(function ($group, $customerId) {
                $first = $group->first();
                $customerName = $first?->customer?->company_name;
                if (! $customerName && $customerId) {
                    $customerName = Customer::find($customerId)?->company_name;
                }

                return [
                    'customer' => $customerName ?: 'Unknown',
                    'invoices' => (int) $group->count(),
                    'revenue' => (float) $group->sum('total'),
                    'due' => (float) $group->sum('due_amount'),
                    'last_invoice' => optional($group->pluck('invoice_date')->filter()->sort()->last())->format('M d, Y'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        return [
            'eyebrow' => 'Analytics & Reporting',
            'headline' => 'Review daily orders, sales, dues, supplier payables, stock, and profitability.',
            'description' => 'These report cards are computed from the ERP records currently stored in the database.',
            'actions' => ['Run Report', 'Export Excel'],
            'report_filters' => [
                'period' => $period,
                'month' => $selectedMonth,
                'submit_url' => route('portal.page', ['page' => 'reports']),
            ],
            'chart' => [
                'title' => 'Revenue vs Expense vs Profit',
                'labels' => $labels->all(),
                'series' => [
                    'revenue' => $revenueSeries->map(fn (float $value) => round($value, 2))->all(),
                    'expense' => $expenseSeries->map(fn (float $value) => round($value, 2))->all(),
                    'profit' => $profitSeries->map(fn (float $value) => round($value, 2))->all(),
                ],
            ],
            'stats' => [
                ['label' => 'Sales Total', 'value' => '$' . number_format($sales, 0), 'note' => 'invoice-backed sales'],
                ['label' => 'Expense Total', 'value' => '$' . number_format($expenses, 0), 'note' => 'recorded operating costs'],
                ['label' => 'Receivables', 'value' => '$' . number_format((float) $invoiceRows->sum('due_amount'), 0), 'note' => 'customer dues'],
                ['label' => 'Profit Estimate', 'value' => '$' . number_format($sales - $expenses, 0), 'note' => 'sales minus expenses'],
            ],
            'table' => [
                'title' => 'Available reports',
                'columns' => ['Report', 'Description', 'Source', 'Cadence', 'Format', 'Status'],
                'rows' => [
                    ['Sales Report', 'Invoice totals by customer and date', 'Invoices', 'Daily', 'Excel/PDF', 'Ready'],
                    ['Due Report', 'Outstanding invoice balances', 'Invoices', 'Daily', 'Excel', 'Ready'],
                    ['Inventory Report', 'Material stock and low alerts', 'Raw Materials', 'Live', 'Excel', 'Ready'],
                    ['Purchase Payables', 'Supplier due balances', 'Purchase Orders', 'Daily', 'Excel', 'Ready'],
                ],
            ],
            'secondary_table' => [
                'title' => 'Top Customer Report',
                'columns' => ['Customer', 'Invoices', 'Revenue', 'Due', 'Last Invoice'],
                'rows' => $customerRows->map(fn (array $row) => [
                    $row['customer'],
                    (string) $row['invoices'],
                    '$' . number_format($row['revenue'], 2),
                    '$' . number_format($row['due'], 2),
                    $row['last_invoice'] ?: '-',
                ])->all(),
                'is_report' => true,
            ],
            'side_panel' => [
                'title' => 'Core reports',
                'items' => ['Daily orders', 'Sales report', 'Due report', 'Supplier payables', 'Stock report', 'Low material report', 'Customer-wise sales', 'Order profitability'],
            ],
        ];
    }

    private function usersRolesData(Tenant $tenant): array
    {
        $users = User::with(['roles', 'permissions'])->where('tenant_id', $tenant->id)->latest()->get();
        $roles = Role::where('tenant_id', $tenant->id)->latest()->get();

        return [
            'eyebrow' => 'Access Management',
            'headline' => 'Manage tenant users, roles, permissions, departments, and operational restrictions.',
            'description' => 'Users, roles, permissions, and employees are all persisted in the database and linked together.',
            'actions' => ['Add User', 'Add Role'],
            'stats' => [
                ['label' => 'Users', 'value' => (string) $users->count(), 'note' => 'tenant users'],
                ['label' => 'Roles', 'value' => (string) Role::where('tenant_id', $tenant->id)->count(), 'note' => 'role profiles'],
                ['label' => 'Employees', 'value' => (string) Employee::where('tenant_id', $tenant->id)->count(), 'note' => 'employee records'],
                ['label' => 'Permissions', 'value' => (string) $users->flatMap->permissions->pluck('id')->unique()->count(), 'note' => 'granted permissions'],
            ],
            'table' => [
                'title' => 'User directory',
                'columns' => ['Name', 'Email', 'Phone', 'Role', 'Last Login', 'Status'],
                'rows' => $users->map(fn (User $user) => [
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->roles->first()?->name ?? '-',
                    optional($user->last_login_at)->diffForHumans(),
                    str($user->status)->headline()->toString(),
                ])->all(),
                'record_ids' => $users->pluck('id')->all(),
                'module' => 'users',
            ],
            'secondary_table' => [
                'title' => 'Role directory',
                'columns' => ['Role Name', 'Guard', 'Created'],
                'rows' => $roles->map(fn (Role $role) => [
                    $role->name,
                    $role->guard_name,
                    optional($role->created_at)->format('M d, Y'),
                ])->all(),
                'record_ids' => $roles->pluck('id')->all(),
                'module' => 'roles',
            ],
            'side_panel' => [
                'title' => 'Roles',
                'items' => Role::where('tenant_id', $tenant->id)->pluck('name')->all(),
            ],
        ];
    }

    private function settingsData(Tenant $tenant): array
    {
        $settings = Setting::where('tenant_id', $tenant->id)->get();
        $companyProfile = (array) optional(
            Setting::where('tenant_id', $tenant->id)->where('key', 'company_profile')->first()
        )->value_json;
        $paperTypes = PaperType::where(fn ($q) => $q->where('tenant_id', $tenant->id)->orWhereNull('tenant_id'))->get();
        $inkTypes = InkType::where('tenant_id', $tenant->id)->get();
        $standardSheets = StandardSheet::where('tenant_id', $tenant->id)->get();
        $units = Unit::where('tenant_id', $tenant->id)->get();

        $masterRows = collect();
        foreach ($paperTypes as $paperType) {
            $masterRows->push([
                'module' => 'paper-types',
                'id' => $paperType->id,
                'type' => 'Paper Type',
                'name' => $paperType->name,
                'code' => $paperType->code,
                'extra' => $paperType->notes,
                'status' => $paperType->status,
                'updated_at' => $paperType->updated_at,
            ]);
        }
        foreach ($inkTypes as $inkType) {
            $masterRows->push([
                'module' => 'ink-types',
                'id' => $inkType->id,
                'type' => 'Ink Type',
                'name' => $inkType->name,
                'code' => $inkType->code,
                'extra' => $inkType->pantone_code,
                'status' => $inkType->status,
                'updated_at' => $inkType->updated_at,
            ]);
        }
        foreach ($standardSheets as $standardSheet) {
            $masterRows->push([
                'module' => 'standard-sheets',
                'id' => $standardSheet->id,
                'type' => 'Standard Sheet',
                'name' => $standardSheet->name,
                'code' => $standardSheet->code,
                'extra' => $standardSheet->width_in . ' x ' . $standardSheet->height_in . ' in',
                'status' => $standardSheet->status,
                'updated_at' => $standardSheet->updated_at,
            ]);
        }
        foreach ($units as $unit) {
            $masterRows->push([
                'module' => 'units',
                'id' => $unit->id,
                'type' => 'Unit',
                'name' => $unit->name,
                'code' => $unit->symbol,
                'extra' => $unit->category . ' / ' . $unit->base_quantity,
                'status' => $unit->status,
                'updated_at' => $unit->updated_at,
            ]);
        }

        $settingsTabs = [
            [
                'key' => 'paper-types',
                'label' => 'Paper Types',
                'create_url' => route('modules.create', 'paper-types'),
                'columns' => ['Name', 'Code', 'Notes', 'Updated', 'Status'],
                'rows' => $paperTypes->map(fn (PaperType $paperType) => [
                    'record_id' => $paperType->id,
                    'module' => 'paper-types',
                    'cells' => [
                        $paperType->name,
                        $paperType->code,
                        $paperType->notes,
                        optional($paperType->updated_at)->format('M d, Y'),
                        str($paperType->status)->headline()->toString(),
                    ],
                ])->values()->all(),
            ],
            [
                'key' => 'ink-types',
                'label' => 'Ink Types',
                'create_url' => route('modules.create', 'ink-types'),
                'columns' => ['Name', 'Code', 'Pantone', 'Updated', 'Status'],
                'rows' => $inkTypes->map(fn (InkType $inkType) => [
                    'record_id' => $inkType->id,
                    'module' => 'ink-types',
                    'cells' => [
                        $inkType->name,
                        $inkType->code,
                        $inkType->pantone_code,
                        optional($inkType->updated_at)->format('M d, Y'),
                        str($inkType->status)->headline()->toString(),
                    ],
                ])->values()->all(),
            ],
            [
                'key' => 'standard-sheets',
                'label' => 'Standard Sheets',
                'create_url' => route('modules.create', 'standard-sheets'),
                'columns' => ['Name', 'Code', 'Size (inch)', 'Updated', 'Status'],
                'rows' => $standardSheets->map(fn (StandardSheet $sheet) => [
                    'record_id' => $sheet->id,
                    'module' => 'standard-sheets',
                    'cells' => [
                        $sheet->name,
                        $sheet->code,
                        $sheet->width_in . ' x ' . $sheet->height_in,
                        optional($sheet->updated_at)->format('M d, Y'),
                        str($sheet->status)->headline()->toString(),
                    ],
                ])->values()->all(),
            ],
            [
                'key' => 'units',
                'label' => 'Units',
                'create_url' => route('modules.create', 'units'),
                'columns' => ['Name', 'Symbol', 'Category', 'Base Qty', 'Status'],
                'rows' => $units->map(fn (Unit $unit) => [
                    'record_id' => $unit->id,
                    'module' => 'units',
                    'cells' => [
                        $unit->name,
                        $unit->symbol,
                        $unit->category,
                        (string) $unit->base_quantity,
                        str($unit->status)->headline()->toString(),
                    ],
                ])->values()->all(),
            ],
        ];

        return [
            'eyebrow' => 'Workspace Settings',
            'headline' => 'Configure tenant profile, branches, tax rules, numbering, and master print settings.',
            'description' => 'Paper Types, Ink Types, Standard Sheets, and Units are managed here with full CRUD.',
            'actions' => ['Save Settings', 'Preview Branding'],
            'stats' => [
                ['label' => 'Setting Groups', 'value' => (string) $settings->count(), 'note' => 'saved tenant settings'],
                ['label' => 'Branches', 'value' => (string) Warehouse::where('tenant_id', $tenant->id)->count(), 'note' => 'configured operational locations'],
                ['label' => 'Users', 'value' => (string) User::where('tenant_id', $tenant->id)->count(), 'note' => 'workspace staff count'],
                ['label' => 'Master Records', 'value' => (string) $masterRows->count(), 'note' => 'paper/ink/sheet/unit entries'],
            ],
            'table' => [
                'title' => 'Print Master Settings',
                'columns' => ['Type', 'Name', 'Code', 'Details', 'Updated', 'Status'],
                'rows' => $masterRows->sortByDesc('updated_at')->map(fn (array $row) => [
                    $row['type'],
                    $row['name'],
                    $row['code'],
                    $row['extra'],
                    optional($row['updated_at'])->format('M d, Y'),
                    str($row['status'] ?? 'active')->headline()->toString(),
                ])->values()->all(),
                'record_ids' => $masterRows->sortByDesc('updated_at')->pluck('id')->values()->all(),
                'module_map' => $masterRows->sortByDesc('updated_at')->pluck('module')->values()->all(),
            ],
            'settings_tabs' => $settingsTabs,
            'company_profile' => [
                'company_name' => $companyProfile['company_name'] ?? $tenant->name,
                'tagline' => $companyProfile['tagline'] ?? null,
                'address' => $companyProfile['address'] ?? $tenant->address,
                'phone' => $companyProfile['phone'] ?? $tenant->phone,
                'email' => $companyProfile['email'] ?? $tenant->email,
                'website' => $companyProfile['website'] ?? null,
                'vat_no' => $companyProfile['vat_no'] ?? null,
                'bin_no' => $companyProfile['bin_no'] ?? null,
                'trade_license_no' => $companyProfile['trade_license_no'] ?? null,
                'logo_url' => $companyProfile['logo_url'] ?? $tenant->logo,
                'signature_name' => $companyProfile['signature_name'] ?? null,
                'signature_title' => $companyProfile['signature_title'] ?? null,
                'quotation_footer_note' => $companyProfile['quotation_footer_note'] ?? null,
            ],
            'side_panel' => [
                'title' => 'Master CRUD Shortcuts',
                'items' => [
                    'Add Paper Type: ' . route('modules.create', 'paper-types'),
                    'Add Ink Type: ' . route('modules.create', 'ink-types'),
                    'Add Standard Sheet: ' . route('modules.create', 'standard-sheets'),
                    'Add Unit: ' . route('modules.create', 'units'),
                ],
            ],
        ];
    }

    private function subscriptionData(Tenant $tenant): array
    {
        $plans = SubscriptionPlan::latest()->get();

        return [
            'eyebrow' => 'SaaS Billing',
            'headline' => 'Manage plans, trials, billing cycle, usage limits, upgrades, and tenant status.',
            'description' => 'Subscription plans and tenant plan assignment are both stored in the database now.',
            'actions' => ['Upgrade Plan', 'Billing History'],
            'stats' => [
                ['label' => 'Current Plan', 'value' => $tenant->plan?->name ?? 'N/A', 'note' => 'tenant subscription'],
                ['label' => 'Plan Status', 'value' => str($tenant->subscription_status)->headline()->toString(), 'note' => 'tenant billing state'],
                ['label' => 'Users Used', 'value' => User::where('tenant_id', $tenant->id)->count() . '/' . ($tenant->plan?->max_users ?? '-'), 'note' => 'seat usage'],
                ['label' => 'Warehouses', 'value' => (string) Warehouse::where('tenant_id', $tenant->id)->count(), 'note' => 'resource usage'],
            ],
            'table' => [
                'title' => 'Plan comparison',
                'columns' => ['Plan', 'Users', 'Orders / Month', 'Warehouses', 'Storage', 'Status'],
                'rows' => $plans->map(fn (SubscriptionPlan $plan) => [
                    $plan->name,
                    (string) $plan->max_users,
                    $plan->max_orders_per_month ? (string) $plan->max_orders_per_month : 'Unlimited',
                    $plan->max_warehouses ? (string) $plan->max_warehouses : 'Unlimited',
                    $plan->max_storage_mb ? $plan->max_storage_mb . ' MB' : 'Unlimited',
                    '$' . number_format((float) $plan->monthly_price, 0) . '/mo',
                ])->all(),
            ],
            'side_panel' => [
                'title' => 'Billing controls',
                'items' => ['Trial period', 'Monthly or yearly billing', 'Upgrade / downgrade', 'Usage limits', 'Suspension rules'],
            ],
        ];
    }
}
