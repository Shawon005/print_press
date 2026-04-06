<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\RawMaterial;
use App\Models\RawMaterialCategory;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ModuleRecordController extends Controller
{
    public function create(string $module): View
    {
        $config = $this->config($module);
        abort_unless($config, 404);

        return view('modules.create', [
            'module' => $module,
            'config' => $config,
            'options' => $this->options(),
            'record' => null,
            'formAction' => route('modules.store', $module),
            'formMethod' => 'POST',
        ]);
    }

    public function edit(string $module, int $id): View
    {
        $config = $this->config($module);
        abort_unless($config, 404);

        $record = $config['model']::findOrFail($id);

        return view('modules.create', [
            'module' => $module,
            'config' => array_merge($config, ['title' => 'Edit ' . rtrim($config['title'], 'e')]),
            'options' => $this->options(),
            'record' => $record,
            'formAction' => route('modules.update', [$module, $id]),
            'formMethod' => 'PUT',
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $config = $this->config($module);
        abort_unless($config, 404);

        $data = $request->validate($config['rules']);
        $tenant = $this->tenant();
        $user = $request->user();

        $payload = ($config['payload'])($data, $tenant, $user);
        $record = $config['model']::create($payload);
        if (isset($config['after_store'])) {
            $config['after_store']($record, $data);
        }

        return redirect()->route($this->redirectPage($module), $this->redirectParams($module))
            ->with('success', $config['success']);
    }

    public function update(Request $request, string $module, int $id): RedirectResponse
    {
        $config = $this->config($module);
        abort_unless($config, 404);

        $data = $request->validate($config['rules']);
        $tenant = $this->tenant();
        $user = $request->user();
        $record = $config['model']::findOrFail($id);

        $payload = ($config['payload'])($data, $tenant, $user);
        $record->update($payload);
        if (isset($config['after_update'])) {
            $config['after_update']($record, $data);
        }

        return redirect()->route($this->redirectPage($module), $this->redirectParams($module))
            ->with('success', rtrim($config['success'], '.') . ' updated.');
    }

    public function destroy(string $module, int $id): RedirectResponse
    {
        $config = $this->config($module);
        abort_unless($config, 404);

        $record = $config['model']::findOrFail($id);
        $record->delete();

        return redirect()->route($this->redirectPage($module), $this->redirectParams($module))
            ->with('success', str($module)->headline() . ' record deleted successfully.');
    }

    public function updateOrderStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $data['status']]);

        return redirect()->route('portal.page', ['page' => 'orders'])
            ->with('success', 'Order status updated successfully.');
    }

    public function export(string $module)
    {
        $config = $this->config($module);
        abort_unless($config, 404);

        $tenant = $this->tenant();
        $rows = ($config['export'])($tenant);

        $csv = collect($rows)->map(fn ($row) => collect($row)->map(function ($value) {
            $value = str_replace('"', '""', (string) $value);
            return '"' . $value . '"';
        })->implode(','))->implode("\n");

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $module . '-export.csv"',
        ]);
    }

    private function tenant(): Tenant
    {
        return Tenant::firstOrFail();
    }

    private function redirectPage(string $module): string
    {
        return in_array($module, ['users', 'roles'], true) ? 'portal.page' : ($module === 'dashboard' ? 'portal.home' : 'portal.page');
    }

    private function redirectParams(string $module): array
    {
        if (in_array($module, ['users', 'roles'], true)) {
            return ['page' => 'users-roles'];
        }

        return $module === 'dashboard' ? [] : ['page' => $module];
    }

    private function options(): array
    {
        $tenant = $this->tenant();

        return [
            'customers' => Customer::where('tenant_id', $tenant->id)->pluck('company_name', 'id')->all(),
            'suppliers' => Supplier::where('tenant_id', $tenant->id)->pluck('company_name', 'id')->all(),
            'products' => Product::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'product_categories' => ProductCategory::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'raw_materials' => RawMaterial::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'raw_material_categories' => RawMaterialCategory::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'warehouses' => Warehouse::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'quotations' => Quotation::where('tenant_id', $tenant->id)->pluck('quote_number', 'id')->all(),
            'orders' => Order::where('tenant_id', $tenant->id)->pluck('order_number', 'id')->all(),
            'users' => User::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'roles' => \App\Models\Role::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
        ];
    }

    private function config(string $module): ?array
    {
        return [
            'customers' => [
                'title' => 'Add Customer',
                'model' => Customer::class,
                'success' => 'Customer created successfully.',
                'fields' => [
                    ['name' => 'customer_code', 'label' => 'Customer Code', 'type' => 'text'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text'],
                    ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'city', 'label' => 'City', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'vip' => 'VIP', 'lead' => 'Lead']],
                ],
                'rules' => [
                    'customer_code' => ['required', 'string', 'max:255'],
                    'company_name' => ['required', 'string', 'max:255'],
                    'contact_person' => ['nullable', 'string', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:255'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'city' => ['nullable', 'string', 'max:255'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Code', 'Company', 'Contact', 'Phone', 'Email', 'City', 'Status']], Customer::where('tenant_id', $tenant->id)->get(['customer_code', 'company_name', 'contact_person', 'phone', 'email', 'city', 'status'])->map(fn ($c) => [$c->customer_code, $c->company_name, $c->contact_person, $c->phone, $c->email, $c->city, $c->status])->all()),
            ],
            'suppliers' => [
                'title' => 'Add Supplier',
                'model' => Supplier::class,
                'success' => 'Supplier created successfully.',
                'fields' => [
                    ['name' => 'supplier_code', 'label' => 'Supplier Code', 'type' => 'text'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text'],
                    ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'preferred' => 'Preferred', 'on_hold' => 'On Hold']],
                ],
                'rules' => [
                    'supplier_code' => ['required', 'string', 'max:255'],
                    'company_name' => ['required', 'string', 'max:255'],
                    'contact_person' => ['nullable', 'string', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:255'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Code', 'Company', 'Contact', 'Phone', 'Email', 'Status']], Supplier::where('tenant_id', $tenant->id)->get(['supplier_code', 'company_name', 'contact_person', 'phone', 'email', 'status'])->map(fn ($s) => [$s->supplier_code, $s->company_name, $s->contact_person, $s->phone, $s->email, $s->status])->all()),
            ],
            'products' => [
                'title' => 'Add Product',
                'model' => Product::class,
                'success' => 'Product created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Product Name', 'type' => 'text'],
                    ['name' => 'sku', 'label' => 'SKU', 'type' => 'text'],
                    ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'source' => 'product_categories'],
                    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['service' => 'Service', 'item' => 'Item']],
                    ['name' => 'base_price', 'label' => 'Base Price', 'type' => 'number'],
                    ['name' => 'unit', 'label' => 'Unit', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'draft' => 'Draft']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'sku' => ['nullable', 'string', 'max:255'],
                    'category_id' => ['nullable', 'integer'],
                    'type' => ['required', 'string'],
                    'base_price' => ['required', 'numeric'],
                    'unit' => ['required', 'string', 'max:255'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['SKU', 'Name', 'Type', 'Base Price', 'Unit', 'Status']], Product::where('tenant_id', $tenant->id)->get(['sku', 'name', 'type', 'base_price', 'unit', 'status'])->map(fn ($p) => [$p->sku, $p->name, $p->type, $p->base_price, $p->unit, $p->status])->all()),
            ],
            'raw-materials' => [
                'title' => 'Add Raw Material',
                'model' => RawMaterial::class,
                'success' => 'Raw material created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Material Name', 'type' => 'text'],
                    ['name' => 'code', 'label' => 'Code', 'type' => 'text'],
                    ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'source' => 'raw_material_categories'],
                    ['name' => 'unit', 'label' => 'Unit', 'type' => 'text'],
                    ['name' => 'current_stock', 'label' => 'Current Stock', 'type' => 'number'],
                    ['name' => 'minimum_stock', 'label' => 'Minimum Stock', 'type' => 'number'],
                    ['name' => 'average_cost', 'label' => 'Average Cost', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['required', 'string', 'max:255'],
                    'category_id' => ['nullable', 'integer'],
                    'unit' => ['required', 'string', 'max:255'],
                    'current_stock' => ['required', 'numeric'],
                    'minimum_stock' => ['required', 'numeric'],
                    'average_cost' => ['required', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Code', 'Name', 'Unit', 'Current Stock', 'Minimum Stock', 'Average Cost', 'Status']], RawMaterial::where('tenant_id', $tenant->id)->get(['code', 'name', 'unit', 'current_stock', 'minimum_stock', 'average_cost', 'status'])->map(fn ($m) => [$m->code, $m->name, $m->unit, $m->current_stock, $m->minimum_stock, $m->average_cost, $m->status])->all()),
            ],
            'warehouses' => [
                'title' => 'Add Warehouse',
                'model' => Warehouse::class,
                'success' => 'Warehouse created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Warehouse Name', 'type' => 'text'],
                    ['name' => 'code', 'label' => 'Code', 'type' => 'text'],
                    ['name' => 'manager_name', 'label' => 'Manager Name', 'type' => 'text'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['required', 'string', 'max:255'],
                    'manager_name' => ['nullable', 'string', 'max:255'],
                    'address' => ['nullable', 'string', 'max:255'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Code', 'Name', 'Manager', 'Address', 'Status']], Warehouse::where('tenant_id', $tenant->id)->get(['code', 'name', 'manager_name', 'address', 'status'])->map(fn ($w) => [$w->code, $w->name, $w->manager_name, $w->address, $w->status])->all()),
            ],
            'quotations' => [
                'title' => 'Create Quotation',
                'model' => Quotation::class,
                'success' => 'Quotation created successfully.',
                'fields' => [
                    ['name' => 'quote_number', 'label' => 'Quote Number', 'type' => 'text'],
                    ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'source' => 'customers'],
                    ['name' => 'inquiry_date', 'label' => 'Inquiry Date', 'type' => 'date'],
                    ['name' => 'valid_until', 'label' => 'Valid Until', 'type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number'],
                    ['name' => 'discount', 'label' => 'Discount', 'type' => 'number'],
                    ['name' => 'tax', 'label' => 'Tax', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'sent' => 'Sent', 'approved' => 'Approved']],
                ],
                'rules' => [
                    'quote_number' => ['required', 'string', 'max:255'],
                    'customer_id' => ['required', 'integer'],
                    'inquiry_date' => ['nullable', 'date'],
                    'valid_until' => ['nullable', 'date'],
                    'subtotal' => ['required', 'numeric'],
                    'discount' => ['nullable', 'numeric'],
                    'tax' => ['nullable', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant, $user) => array_merge($data, [
                    'tenant_id' => $tenant->id,
                    'total' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'created_by' => $user?->id,
                    'approved_at' => ($data['status'] ?? null) === 'approved' ? now() : null,
                ]),
                'export' => fn ($tenant) => array_merge([['Quote Number', 'Customer ID', 'Inquiry Date', 'Valid Until', 'Total', 'Status']], Quotation::where('tenant_id', $tenant->id)->get(['quote_number', 'customer_id', 'inquiry_date', 'valid_until', 'total', 'status'])->map(fn ($q) => [$q->quote_number, $q->customer_id, $q->inquiry_date, $q->valid_until, $q->total, $q->status])->all()),
            ],
            'orders' => [
                'title' => 'Create Order',
                'model' => Order::class,
                'success' => 'Order created successfully.',
                'fields' => [
                    ['name' => 'order_number', 'label' => 'Order Number', 'type' => 'text'],
                    ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'source' => 'customers'],
                    ['name' => 'quotation_id', 'label' => 'Quotation', 'type' => 'select', 'source' => 'quotations', 'nullable' => true],
                    ['name' => 'job_title', 'label' => 'Job Title', 'type' => 'text'],
                    ['name' => 'order_date', 'label' => 'Order Date', 'type' => 'date'],
                    ['name' => 'expected_delivery_date', 'label' => 'Expected Delivery Date', 'type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number'],
                    ['name' => 'discount', 'label' => 'Discount', 'type' => 'number'],
                    ['name' => 'tax', 'label' => 'Tax', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approval' => 'Approval', 'printing' => 'Printing', 'finishing' => 'Finishing']],
                ],
                'rules' => [
                    'order_number' => ['required', 'string', 'max:255'],
                    'customer_id' => ['required', 'integer'],
                    'quotation_id' => ['nullable', 'integer'],
                    'job_title' => ['required', 'string', 'max:255'],
                    'order_date' => ['nullable', 'date'],
                    'expected_delivery_date' => ['nullable', 'date'],
                    'subtotal' => ['required', 'numeric'],
                    'discount' => ['nullable', 'numeric'],
                    'tax' => ['nullable', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant, $user) => array_merge($data, [
                    'tenant_id' => $tenant->id,
                    'total' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'paid_amount' => 0,
                    'due_amount' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'created_by' => $user?->id,
                    'assigned_manager_id' => $user?->id,
                    'priority' => 'normal',
                ]),
                'export' => fn ($tenant) => array_merge([['Order Number', 'Customer ID', 'Job Title', 'Order Date', 'Expected Delivery', 'Total', 'Status']], Order::where('tenant_id', $tenant->id)->get(['order_number', 'customer_id', 'job_title', 'order_date', 'expected_delivery_date', 'total', 'status'])->map(fn ($o) => [$o->order_number, $o->customer_id, $o->job_title, $o->order_date, $o->expected_delivery_date, $o->total, $o->status])->all()),
            ],
            'purchases' => [
                'title' => 'Create Purchase Order',
                'model' => PurchaseOrder::class,
                'success' => 'Purchase order created successfully.',
                'fields' => [
                    ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'text'],
                    ['name' => 'supplier_id', 'label' => 'Supplier', 'type' => 'select', 'source' => 'suppliers'],
                    ['name' => 'warehouse_id', 'label' => 'Warehouse', 'type' => 'select', 'source' => 'warehouses'],
                    ['name' => 'order_date', 'label' => 'Order Date', 'type' => 'date'],
                    ['name' => 'expected_date', 'label' => 'Expected Date', 'type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number'],
                    ['name' => 'discount', 'label' => 'Discount', 'type' => 'number'],
                    ['name' => 'tax', 'label' => 'Tax', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'ordered' => 'Ordered', 'partial_received' => 'Partial Received']],
                ],
                'rules' => [
                    'po_number' => ['required', 'string', 'max:255'],
                    'supplier_id' => ['required', 'integer'],
                    'warehouse_id' => ['nullable', 'integer'],
                    'order_date' => ['nullable', 'date'],
                    'expected_date' => ['nullable', 'date'],
                    'subtotal' => ['required', 'numeric'],
                    'discount' => ['nullable', 'numeric'],
                    'tax' => ['nullable', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant, $user) => array_merge($data, [
                    'tenant_id' => $tenant->id,
                    'total' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'paid_amount' => 0,
                    'due_amount' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'created_by' => $user?->id,
                ]),
                'export' => fn ($tenant) => array_merge([['PO Number', 'Supplier ID', 'Warehouse ID', 'Order Date', 'Expected Date', 'Total', 'Status']], PurchaseOrder::where('tenant_id', $tenant->id)->get(['po_number', 'supplier_id', 'warehouse_id', 'order_date', 'expected_date', 'total', 'status'])->map(fn ($p) => [$p->po_number, $p->supplier_id, $p->warehouse_id, $p->order_date, $p->expected_date, $p->total, $p->status])->all()),
            ],
            'invoices' => [
                'title' => 'Create Invoice',
                'model' => Invoice::class,
                'success' => 'Invoice created successfully.',
                'fields' => [
                    ['name' => 'invoice_number', 'label' => 'Invoice Number', 'type' => 'text'],
                    ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'source' => 'customers'],
                    ['name' => 'order_id', 'label' => 'Order', 'type' => 'select', 'source' => 'orders', 'nullable' => true],
                    ['name' => 'invoice_date', 'label' => 'Invoice Date', 'type' => 'date'],
                    ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number'],
                    ['name' => 'discount', 'label' => 'Discount', 'type' => 'number'],
                    ['name' => 'tax', 'label' => 'Tax', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'due' => 'Due', 'paid' => 'Paid']],
                ],
                'rules' => [
                    'invoice_number' => ['required', 'string', 'max:255'],
                    'customer_id' => ['required', 'integer'],
                    'order_id' => ['nullable', 'integer'],
                    'invoice_date' => ['nullable', 'date'],
                    'due_date' => ['nullable', 'date'],
                    'subtotal' => ['required', 'numeric'],
                    'discount' => ['nullable', 'numeric'],
                    'tax' => ['nullable', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => function ($data, $tenant, $user) {
                    $total = ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);

                    return array_merge($data, [
                        'tenant_id' => $tenant->id,
                        'total' => $total,
                        'paid_amount' => ($data['status'] ?? null) === 'paid' ? $total : 0,
                        'due_amount' => ($data['status'] ?? null) === 'paid' ? 0 : $total,
                        'created_by' => $user?->id,
                    ]);
                },
                'export' => fn ($tenant) => array_merge([['Invoice Number', 'Customer ID', 'Invoice Date', 'Due Date', 'Total', 'Status']], Invoice::where('tenant_id', $tenant->id)->get(['invoice_number', 'customer_id', 'invoice_date', 'due_date', 'total', 'status'])->map(fn ($i) => [$i->invoice_number, $i->customer_id, $i->invoice_date, $i->due_date, $i->total, $i->status])->all()),
            ],
            'expenses' => [
                'title' => 'Add Expense',
                'model' => Expense::class,
                'success' => 'Expense created successfully.',
                'fields' => [
                    ['name' => 'expense_date', 'label' => 'Expense Date', 'type' => 'date'],
                    ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => ['Utility' => 'Utility', 'Transport' => 'Transport', 'Maintenance' => 'Maintenance', 'Office' => 'Office']],
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                    ['name' => 'reference_no', 'label' => 'Reference No', 'type' => 'text'],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number'],
                ],
                'rules' => [
                    'expense_date' => ['nullable', 'date'],
                    'category' => ['required', 'string', 'max:255'],
                    'title' => ['required', 'string', 'max:255'],
                    'reference_no' => ['nullable', 'string', 'max:255'],
                    'amount' => ['required', 'numeric'],
                ],
                'payload' => fn ($data, $tenant, $user) => array_merge($data, ['tenant_id' => $tenant->id, 'created_by' => $user?->id]),
                'export' => fn ($tenant) => array_merge([['Date', 'Category', 'Title', 'Reference No', 'Amount']], Expense::where('tenant_id', $tenant->id)->get(['expense_date', 'category', 'title', 'reference_no', 'amount'])->map(fn ($e) => [$e->expense_date, $e->category, $e->title, $e->reference_no, $e->amount])->all()),
            ],
            'deliveries' => [
                'title' => 'Create Delivery',
                'model' => Delivery::class,
                'success' => 'Delivery created successfully.',
                'fields' => [
                    ['name' => 'delivery_number', 'label' => 'Delivery Number', 'type' => 'text'],
                    ['name' => 'order_id', 'label' => 'Order', 'type' => 'select', 'source' => 'orders'],
                    ['name' => 'delivery_date', 'label' => 'Delivery Date', 'type' => 'date'],
                    ['name' => 'assigned_to', 'label' => 'Assigned To', 'type' => 'select', 'source' => 'users'],
                    ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text'],
                    ['name' => 'transport_cost', 'label' => 'Transport Cost', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'assigned' => 'Assigned', 'out_for_delivery' => 'Out For Delivery', 'delivered' => 'Delivered']],
                ],
                'rules' => [
                    'delivery_number' => ['required', 'string', 'max:255'],
                    'order_id' => ['required', 'integer'],
                    'delivery_date' => ['nullable', 'date'],
                    'assigned_to' => ['nullable', 'integer'],
                    'vehicle_no' => ['nullable', 'string', 'max:255'],
                    'transport_cost' => ['required', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Delivery Number', 'Order ID', 'Delivery Date', 'Assigned To', 'Vehicle No', 'Transport Cost', 'Status']], Delivery::where('tenant_id', $tenant->id)->get(['delivery_number', 'order_id', 'delivery_date', 'assigned_to', 'vehicle_no', 'transport_cost', 'status'])->map(fn ($d) => [$d->delivery_number, $d->order_id, $d->delivery_date, $d->assigned_to, $d->vehicle_no, $d->transport_cost, $d->status])->all()),
            ],
            'users' => [
                'title' => 'Add User',
                'model' => User::class,
                'success' => 'User created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['name' => 'password', 'label' => 'Password', 'type' => 'password'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                    ['name' => 'role_id', 'label' => 'Role', 'type' => 'select', 'source' => 'roles'],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:255'],
                    'password' => ['nullable', 'string', 'min:4'],
                    'status' => ['required', 'string'],
                    'role_id' => ['required', 'integer'],
                ],
                'payload' => function ($data, $tenant) {
                    $payload = [
                        'tenant_id' => $tenant->id,
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'status' => $data['status'],
                        'email_verified_at' => now(),
                    ];

                    if (! empty($data['password'])) {
                        $payload['password'] = $data['password'];
                    }

                    return $payload;
                },
                'after_store' => fn (User $user, array $data) => $user->roles()->sync([$data['role_id']]),
                'after_update' => fn (User $user, array $data) => $user->roles()->sync([$data['role_id']]),
                'export' => fn ($tenant) => array_merge([['Name', 'Email', 'Phone', 'Status']], User::where('tenant_id', $tenant->id)->get(['name', 'email', 'phone', 'status'])->map(fn ($u) => [$u->name, $u->email, $u->phone, $u->status])->all()),
            ],
            'roles' => [
                'title' => 'Add Role',
                'model' => \App\Models\Role::class,
                'success' => 'Role created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Role Name', 'type' => 'text'],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                ],
                'payload' => fn ($data, $tenant) => [
                    'tenant_id' => $tenant->id,
                    'name' => $data['name'],
                    'guard_name' => 'web',
                ],
                'export' => fn ($tenant) => array_merge([['Role Name']], \App\Models\Role::where('tenant_id', $tenant->id)->get(['name'])->map(fn ($r) => [$r->name])->all()),
            ],
        ][$module] ?? null;
    }
}
