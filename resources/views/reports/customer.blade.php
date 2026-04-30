<x-layouts.app :title="'Customer Report'" :breadcrumb="'Reports / Customer Report'">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">{{ $customer->company_name }}</h2>
            <p class="text-sm text-slate-500">Customer Code: {{ $customer->customer_code ?: '-' }}</p>
        </div>
        <a href="{{ route('portal.page', 'reports') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Back to Reports</a>
    </div>

    <div class="grid gap-4 md:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Total Quotation</p>
            <p class="mt-2 text-xl font-bold text-slate-900">${{ number_format($totals['quotation_total'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Total Order</p>
            <p class="mt-2 text-xl font-bold text-slate-900">${{ number_format($totals['order_total'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Total Invoice</p>
            <p class="mt-2 text-xl font-bold text-slate-900">${{ number_format($totals['invoice_total'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Total Paid</p>
            <p class="mt-2 text-xl font-bold text-emerald-700">${{ number_format($totals['paid_total'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Total Due</p>
            <p class="mt-2 text-xl font-bold text-rose-700">${{ number_format($totals['due_total'], 2) }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h3 class="text-lg font-bold text-slate-900">Quotations ({{ $quotations->count() }})</h3>
            <div class="mt-3 space-y-2 text-sm">
                @forelse($quotations as $quotation)
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="font-semibold text-slate-900">{{ $quotation->quote_number ?: ('Quotation #' . $quotation->id) }}</p>
                        <p class="text-slate-600">{{ optional($quotation->inquiry_date)->format('M d, Y') ?: '-' }} • ${{ number_format((float) $quotation->total, 2) }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">No quotations found.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h3 class="text-lg font-bold text-slate-900">Orders ({{ $orders->count() }})</h3>
            <div class="mt-3 space-y-2 text-sm">
                @forelse($orders as $order)
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="font-semibold text-slate-900">{{ $order->job_number ?: ('Order #' . $order->id) }}</p>
                        <p class="text-slate-600">{{ optional($order->order_date)->format('M d, Y') ?: '-' }} • ${{ number_format((float) $order->estimated_total_price, 2) }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">No orders found.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h3 class="text-lg font-bold text-slate-900">Invoices ({{ $invoices->count() }})</h3>
            <div class="mt-3 space-y-2 text-sm">
                @forelse($invoices as $invoice)
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="font-semibold text-slate-900">{{ $invoice->invoice_number ?: ('Invoice #' . $invoice->id) }}</p>
                        <p class="text-slate-600">{{ optional($invoice->invoice_date)->format('M d, Y') ?: '-' }} • Paid: ${{ number_format((float) $invoice->paid_amount, 2) }} • Due: ${{ number_format((float) $invoice->due_amount, 2) }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">No invoices found.</p>
                @endforelse
            </div>
        </article>
    </div>
</x-layouts.app>
