<x-layouts.app :title="'Financial Dashboard'" :breadcrumb="'Financials / Receivables'">
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-xl font-bold">Receivables Dashboard</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b border-slate-200 text-left text-slate-500">
                    <th class="pb-2">Job</th><th class="pb-2">Customer</th><th class="pb-2">Advance Status</th><th class="pb-2">Balance Due</th><th class="pb-2">Overdue</th>
                </tr>
                </thead>
                <tbody>
                @foreach($jobOrders as $job)
                    <tr class="border-b border-slate-100">
                        <td class="py-2">{{ $job->job_number }}</td>
                        <td class="py-2">{{ $job->customer?->company_name }}</td>
                        <td class="py-2">
                            @if($job->advance_met)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Advance OK</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Advance Pending</span>
                            @endif
                        </td>
                        <td class="py-2">{{ number_format((float) $job->balance_due, 2) }}</td>
                        <td class="py-2">{{ optional($job->due_date)->isPast() && $job->balance_due > 0 ? 'Yes' : 'No' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
