<x-layouts.app :title="'Job Order Details'" :breadcrumb="'Job Orders / Details'">
    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold">{{ $jobOrder->job_number }} - {{ $jobOrder->job_title }}</h2>
                    <p class="text-sm text-slate-500">Customer: {{ $jobOrder->customer?->company_name }}</p>
                </div>
                <a href="{{ route('job-orders.job-card-pdf', $jobOrder) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Generate Job Card PDF</a>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-slate-200 p-3"><p class="text-xs text-slate-500">Status</p><p class="font-semibold">{{ $jobOrder->status }}</p></div>
                <div class="rounded-lg border border-slate-200 p-3"><p class="text-xs text-slate-500">Due Date</p><p class="font-semibold">{{ optional($jobOrder->due_date)->format('d M Y') }}</p></div>
                <div class="rounded-lg border border-slate-200 p-3"><p class="text-xs text-slate-500">Paper</p><p class="font-semibold">{{ $jobOrder->paperType?->name }} / {{ $jobOrder->gsm }} GSM</p></div>
                <div class="rounded-lg border border-slate-200 p-3"><p class="text-xs text-slate-500">Copies</p><p class="font-semibold">{{ number_format($jobOrder->total_copies) }}</p></div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="mb-3 text-lg font-semibold">Status Timeline</h3>
            <div class="flex flex-wrap gap-2 text-sm">
                @foreach(['draft', 'confirmed', 'in_production', 'quality_check', 'delivered'] as $status)
                    <span class="rounded-full px-3 py-1 {{ $jobOrder->status === $status ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' }}">{{ str($status)->headline() }}</span>
                @endforeach
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="mb-3 text-lg font-semibold">Latest Calculation</h3>
                @php($calc = $jobOrder->calculation->sortByDesc('computed_at')->first())
                @if($calc)
                    <ul class="space-y-2 text-sm">
                        <li>Pages per sheet: <strong>{{ $calc->pages_per_sheet }}</strong></li>
                        <li>Raw sheets: <strong>{{ $calc->raw_sheets }}</strong></li>
                        <li>Wastage sheets: <strong>{{ $calc->wastage_sheets }}</strong></li>
                        <li>Total sheets: <strong>{{ $calc->total_sheets }}</strong></li>
                        <li>Reams/Quires/Sheets: <strong>{{ $calc->reams }} / {{ $calc->quires }} / {{ $calc->remainder_sheets }}</strong></li>
                    </ul>
                @else
                    <p class="text-sm text-slate-500">No calculation attached.</p>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="mb-3 text-lg font-semibold">Payment History</h3>
                <div class="space-y-2 text-sm">
                    @forelse($jobOrder->payments as $payment)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
                            <span>{{ $payment->payment_stage }} / {{ $payment->payment_method }}</span>
                            <strong>{{ number_format((float) $payment->amount, 2) }}</strong>
                        </div>
                    @empty
                        <p class="text-slate-500">No payments recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
