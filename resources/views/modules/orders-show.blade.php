<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Details | Printing Press Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">
    <div class="min-h-screen bg-[var(--app-bg)] px-4 py-8 md:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">Orders Module</p>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">Order Details</h1>
                    <p class="mt-1 text-sm text-slate-500">Job Number: {{ $record->job_number }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('portal.page', 'orders') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Back to Orders</a>
                    <a href="{{ route('modules.edit', ['orders', $record->id]) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Edit</a>
                    <a href="{{ route('modules.print', ['orders', $record->id]) }}" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-slate-900/20" target="_blank">Print</a>
                </div>
            </div>

            <section class="grid gap-4 md:grid-cols-4">
                <article class="surface-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Customer</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ $record->customer?->company_name ?? '-' }}</p>
                </article>
                <article class="surface-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Order Date</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ optional($record->order_date)->format('M d, Y') ?? '-' }}</p>
                </article>
                <article class="surface-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Due Date</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ optional($record->due_date)->format('M d, Y') ?? '-' }}</p>
                </article>
                <article class="surface-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</p>
                    <p class="mt-2 inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">{{ str($record->status)->headline() }}</p>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="surface-card p-6">
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Job Information</h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Job Title</dt><dd class="text-right font-semibold text-slate-900">{{ $record->job_title }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Paper Type</dt><dd class="text-right font-semibold text-slate-900">{{ $record->paperType?->name ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">GSM</dt><dd class="text-right font-semibold text-slate-900">{{ $record->gsm }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Ink Type</dt><dd class="text-right font-semibold text-slate-900">{{ $record->ink_type }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Pantone</dt><dd class="text-right font-semibold text-slate-900">{{ $record->pantone_codes ?: '-' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-500">Finish</dt><dd class="text-right font-semibold text-slate-900">{{ str($record->finish_type)->headline() }}</dd></div>
                    </dl>
                </article>

                <article class="surface-card p-6">
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Print Specifications</h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Total Pages</dt><dd class="text-right font-semibold text-slate-900">{{ $record->total_pages }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Page Size</dt><dd class="text-right font-semibold text-slate-900">{{ $record->page_size }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Custom Size</dt><dd class="text-right font-semibold text-slate-900">{{ ($record->custom_width && $record->custom_height) ? $record->custom_width . ' x ' . $record->custom_height . ' in' : '-' }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Total Copies</dt><dd class="text-right font-semibold text-slate-900">{{ number_format((float) $record->total_copies) }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Standard Sheet</dt><dd class="text-right font-semibold text-slate-900">{{ $record->standard_sheet_size }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-500">Colors</dt><dd class="text-right font-semibold text-slate-900">{{ $record->colors }}</dd></div>
                    </dl>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="surface-card p-6">
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Cost Summary</h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Material Cost</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->estimated_material_cost, 2) }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Plate Cost</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->estimated_plate_cost, 2) }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Other Cost</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->estimated_other_cost, 2) }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Total Cost</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->estimated_total_cost, 2) }}</dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Unit Price</dt><dd class="text-right font-semibold text-slate-900">${{ number_format((float) $record->estimated_unit_price, 2) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-500">Estimated Total Price</dt><dd class="text-right text-lg font-black text-slate-900">${{ number_format((float) $record->estimated_total_price, 2) }}</dd></div>
                    </dl>
                </article>

                <article class="surface-card p-6">
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Latest Calculation</h2>
                    @if ($latestCalculation)
                        <dl class="mt-4 grid gap-3 text-sm">
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Pages Per Sheet</dt><dd class="text-right font-semibold text-slate-900">{{ $latestCalculation->pages_per_sheet }}</dd></div>
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Raw Sheets</dt><dd class="text-right font-semibold text-slate-900">{{ number_format((float) $latestCalculation->raw_sheets, 2) }}</dd></div>
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Wastage (%)</dt><dd class="text-right font-semibold text-slate-900">{{ number_format((float) $latestCalculation->wastage_percentage, 2) }}</dd></div>
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Wastage Sheets</dt><dd class="text-right font-semibold text-slate-900">{{ number_format((float) $latestCalculation->wastage_sheets, 2) }}</dd></div>
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Total Sheets</dt><dd class="text-right font-semibold text-slate-900">{{ number_format((float) $latestCalculation->total_sheets, 2) }}</dd></div>
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Reams</dt><dd class="text-right font-semibold text-slate-900">{{ number_format((float) $latestCalculation->reams, 2) }}</dd></div>
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="font-semibold text-slate-500">Quires</dt><dd class="text-right font-semibold text-slate-900">{{ number_format((float) $latestCalculation->quires, 2) }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-500">Remainder Sheets</dt><dd class="text-right font-semibold text-slate-900">{{ number_format((float) $latestCalculation->remainder_sheets, 2) }}</dd></div>
                        </dl>
                    @else
                        <p class="mt-4 text-sm text-slate-500">No calculation has been saved for this order yet.</p>
                    @endif
                </article>
            </section>

            <article class="surface-card p-6">
                <h2 class="text-xl font-black tracking-tight text-slate-900">Design Attachment</h2>
                <div class="mt-4 grid gap-3 text-sm">
                    <p><span class="font-semibold text-slate-500">Source:</span> <span class="font-semibold text-slate-900">{{ str($record->design_source ?: 'customer_provided')->replace('_', ' ')->headline() }}</span></p>
                    @if ($record->design_file_path)
                        <p><span class="font-semibold text-slate-500">File:</span> <a href="{{ asset('storage/' . $record->design_file_path) }}" target="_blank" class="font-semibold text-[var(--brand)] hover:underline">{{ $record->design_file_name ?: 'Open Design File' }}</a></p>
                        @if (str_starts_with((string) $record->design_file_mime, 'image/'))
                            <img src="{{ asset('storage/' . $record->design_file_path) }}" alt="Order design attachment" class="max-h-[480px] w-full rounded-2xl border border-slate-200 object-contain bg-white p-2">
                        @elseif ($record->design_file_mime === 'application/pdf')
                            <iframe src="{{ asset('storage/' . $record->design_file_path) }}" class="h-[520px] w-full rounded-2xl border border-slate-200 bg-white"></iframe>
                        @endif
                    @else
                        <p class="text-slate-500">No design file uploaded for this order.</p>
                    @endif
                </div>
            </article>

            <article class="surface-card p-6">
                <h2 class="text-xl font-black tracking-tight text-slate-900">Notes</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $record->notes ?: 'No notes added.' }}</p>
            </article>
        </div>
    </div>
</body>
</html>
