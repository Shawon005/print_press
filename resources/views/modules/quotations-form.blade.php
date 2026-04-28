<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $record ? 'Edit Quotation' : 'Create Quotation' }} | Printing Press Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans">
<div class="min-h-screen bg-[var(--app-bg)] px-4 py-8 md:px-8" x-data="quotationForm()">
    <div class="mx-auto max-w-6xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">Quotation Module</p>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ $record ? 'Edit Quotation' : 'Create Quotation' }}</h1>
            </div>
            <a href="{{ route('portal.page', 'quotations') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Back to Quotations</a>
        </div>

        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="surface-card p-6 md:p-8 space-y-6">
            @csrf
            @if ($formMethod !== 'POST') @method($formMethod) @endif

            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-semibold">Quotation No.</label>
                    <input name="quote_number" value="{{ old('quote_number', $record?->quote_number ?? ('QTN-' . now()->format('YmdHis')) ) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Customer</label>
                    <select name="customer_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required>
                        <option value="">Select customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $record?->customer_id) == $customer->id)>{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Date</label>
                    <input type="date" name="inquiry_date" value="{{ old('inquiry_date', optional($record?->inquiry_date)->toDateString() ?? now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', optional($record?->valid_until)->toDateString() ?? now()->addDays(7)->toDateString()) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-sm" @input="recalc()">
                    <thead class="bg-slate-100">
                    <tr>
                        <th class="px-3 py-3 text-left">SL</th>
                        <th class="px-3 py-3 text-left">Description</th>
                        <th class="px-3 py-3 text-left">Qty</th>
                        <th class="px-3 py-3 text-left">Unit Price</th>
                        <th class="px-3 py-3 text-left">Total</th>
                        <th class="px-3 py-3 text-left">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template x-for="(item, index) in items" :key="index">
                        <tr class="border-t border-slate-200 align-top">
                            <td class="px-3 py-2" x-text="String(index + 1).padStart(2, '0')"></td>
                            <td class="px-3 py-2 space-y-2">
                                <input :name="`items[${index}][item_name]`" x-model="item.item_name" class="w-full rounded-xl border border-slate-200 px-3 py-2" placeholder="Paper/Board, Plate, Printing, Lamination..." required>
                                <textarea :name="`items[${index}][description]`" x-model="item.description" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2" placeholder="Specs: size, GSM, color, finishing"></textarea>
                            </td>
                            <td class="px-3 py-2"><input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model.number="item.quantity" class="w-28 rounded-xl border border-slate-200 px-3 py-2" required></td>
                            <td class="px-3 py-2"><input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" class="w-32 rounded-xl border border-slate-200 px-3 py-2" required></td>
                            <td class="px-3 py-2 font-semibold" x-text="format(item.quantity * item.unit_price)"></td>
                            <td class="px-3 py-2"><button type="button" @click="remove(index)" class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white">Remove</button></td>
                        </tr>
                    </template>
                    </tbody>
                </table>
                <div class="border-t border-slate-200 p-3">
                    <button type="button" @click="add()" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold">+ Add Row</button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold">Subtotal</label>
                    <input type="text" :value="format(subtotal)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm bg-slate-50" readonly>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Discount</label>
                    <input type="number" step="0.01" min="0" name="discount" x-model.number="discount" value="{{ old('discount', $record?->discount ?? 0) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Tax/VAT</label>
                    <input type="number" step="0.01" min="0" name="tax" x-model.number="tax" value="{{ old('tax', $record?->tax ?? 0) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Profit (%)</label>
                    <input type="number" step="0.01" min="0" name="profit_percentage" x-model.number="profitPercentage" value="{{ old('profit_percentage', $record?->profit_percentage ?? 0) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                    <p class="mt-1 text-xs text-slate-500">Amount: <span x-text="format(profitAmount)"></span></p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Grand Total</label>
                    <input type="text" :value="format(grandTotal)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm bg-slate-50 font-bold" readonly>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold">Status</label>
                    <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                        <option value="draft" @selected(old('status', $record?->status) === 'draft')>Draft</option>
                        <option value="sent" @selected(old('status', $record?->status) === 'sent')>Sent</option>
                        <option value="approved" @selected(old('status', $record?->status) === 'approved')>Approved</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Notes</label>
                    <textarea name="notes" rows="2" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" placeholder="Note: Excluding VAT & TAX / payment terms etc.">{{ old('notes', $record?->notes) }}</textarea>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold">Design Source</label>
                    <select name="design_source" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                        <option value="customer_provided" @selected(old('design_source', $record?->design_source ?? 'customer_provided') === 'customer_provided')>Customer Provided</option>
                        <option value="in_house" @selected(old('design_source', $record?->design_source ?? 'customer_provided') === 'in_house')>In House</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold">Design File (Image/PDF)</label>
                    <input type="file" name="design_file" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                    @if ($record?->design_file_path)
                        <p class="mt-2 text-xs text-slate-500">Current file: <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->design_file_path) }}" target="_blank" class="font-semibold text-[var(--brand)] hover:underline">{{ $record->design_file_name ?: 'View uploaded design' }}</a></p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $record ? 'Update Quotation' : 'Save Quotation' }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function quotationForm() {
    const oldItems = @json(old('items', $items));

    return {
        items: oldItems.length ? oldItems : [
            { item_name: 'Paper/Board', description: '', quantity: 1, unit_price: 0 },
            { item_name: 'Plate', description: '', quantity: 1, unit_price: 0 },
            { item_name: 'Printing', description: '', quantity: 1, unit_price: 0 },
            { item_name: 'Lamination', description: '', quantity: 1, unit_price: 0 },
            { item_name: 'Die Cutting', description: '', quantity: 1, unit_price: 0 },
        ],
        discount: Number(@json((float) old('discount', $record?->discount ?? 0))),
        tax: Number(@json((float) old('tax', $record?->tax ?? 0))),
        profitPercentage: Number(@json((float) old('profit_percentage', $record?->profit_percentage ?? 0))),
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0);
        },
        get profitAmount() {
            return this.subtotal * (Number(this.profitPercentage || 0) / 100);
        },
        get grandTotal() {
            return this.subtotal + this.profitAmount - Number(this.discount || 0) + Number(this.tax || 0);
        },
        add() {
            this.items.push({ item_name: '', description: '', quantity: 1, unit_price: 0 });
        },
        remove(index) {
            if (this.items.length === 1) return;
            this.items.splice(index, 1);
        },
        recalc() {},
        format(value) {
            return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>
</body>
</html>
