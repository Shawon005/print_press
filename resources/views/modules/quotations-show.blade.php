<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation Details | Printing Press Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">
    <div class="min-h-screen bg-[var(--app-bg)] px-4 py-8 md:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">Quotations Module</p>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">Quotation Details</h1>
                    <p class="mt-1 text-sm text-slate-500">Quote Number: {{ $record->quote_number }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('portal.page', 'quotations') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Back to Quotations</a>
                    <a href="{{ route('modules.edit', ['quotations', $record->id]) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Edit</a>
                    <a href="{{ route('modules.print', ['quotations', $record->id]) }}" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-slate-900/20" target="_blank">Print</a>
                </div>
            </div>

            <section class="grid gap-4 md:grid-cols-4">
                <article class="surface-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Customer</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ $record->customer?->company_name ?? '-' }}</p>
                </article>
                <article class="surface-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Inquiry Date</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ optional($record->inquiry_date)->format('M d, Y') ?? '-' }}</p>
                </article>
                <article class="surface-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Valid Until</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ optional($record->valid_until)->format('M d, Y') ?? '-' }}</p>
                </article>
                <article class="surface-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</p>
                    <p class="mt-2 inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">{{ str($record->status)->headline() }}</p>
                </article>
            </section>

            <article class="surface-card p-6">
                <h2 class="text-xl font-black tracking-tight text-slate-900">Quotation Items</h2>
                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full border-separate border-spacing-y-2 text-sm">
                        <thead>
                            <tr class="text-left font-semibold text-slate-500">
                                <th class="px-3 py-2">SL</th>
                                <th class="px-3 py-2">Item</th>
                                <th class="px-3 py-2">Description</th>
                                <th class="px-3 py-2 text-right">Qty</th>
                                <th class="px-3 py-2 text-right">Unit Price</th>
                                <th class="px-3 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($record->items as $item)
                                <tr class="bg-slate-50 text-slate-700 shadow-sm">
                                    <td class="rounded-l-2xl px-3 py-3 font-semibold text-slate-900">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-3 py-3 font-semibold text-slate-900">{{ $item->item_name }}</td>
                                    <td class="px-3 py-3">{{ $item->description ?: '-' }}</td>
                                    <td class="px-3 py-3 text-right">{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td class="px-3 py-3 text-right">${{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="rounded-r-2xl px-3 py-3 text-right font-bold text-slate-900">${{ number_format((float) $item->total_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="rounded-2xl bg-slate-50 px-3 py-4 text-center text-slate-500">No quotation items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="surface-card p-6">
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Amount Summary</h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Subtotal</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->subtotal, 2) }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Profit ({{ number_format((float) $record->profit_percentage, 2) }}%)</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->profit_amount, 2) }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Discount</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->discount, 2) }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Tax/VAT</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->tax, 2) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-500">Grand Total</dt><dd class="text-right text-lg font-black text-slate-900">${{ number_format((float) $record->total, 2) }}</dd></div>
                    </dl>
                </article>

                <article class="surface-card p-6">
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Approval</h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Approved At</dt><dd class="text-right font-semibold text-slate-900">{{ optional($record->approved_at)->format('M d, Y h:i A') ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-500">Converted Order ID</dt><dd class="text-right font-semibold text-slate-900">{{ $record->converted_to_order_id ?: '-' }}</dd></div>
                    </dl>

                    <h3 class="mt-6 text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Notes</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">{{ $record->notes ?: 'No notes added.' }}</p>
                </article>
            </section>

            <article class="surface-card p-6">
                <h2 class="text-xl font-black tracking-tight text-slate-900">Design Attachment</h2>
                <div class="mt-4 grid gap-3 text-sm">
                    <p><span class="font-semibold text-slate-500">Source:</span> <span class="font-semibold text-slate-900">{{ str($record->design_source ?: 'customer_provided')->replace('_', ' ')->headline() }}</span></p>
                    @if ($record->design_file_path)
                        <p><span class="font-semibold text-slate-500">File:</span> <a href="{{ asset('storage/' . $record->design_file_path) }}" target="_blank" class="font-semibold text-[var(--brand)] hover:underline">{{ $record->design_file_name ?: 'Open Design File' }}</a></p>
                        @if (str_starts_with((string) $record->design_file_mime, 'image/'))
                            <img src="{{ asset('storage/' . $record->design_file_path) }}" alt="Quotation design attachment" class="max-h-[480px] w-full rounded-2xl border border-slate-200 object-contain bg-white p-2">
                        @elseif ($record->design_file_mime === 'application/pdf')
                            <iframe src="{{ asset('storage/' . $record->design_file_path) }}}" class="h-[520px] w-full rounded-2xl border border-slate-200 bg-white"></iframe>
                        @endif
                    @else
                        <p class="text-slate-500">No design file uploaded for this quotation.</p>
                    @endif
                </div>
            </article>
        </div>
    </div>
</body>
</html>
