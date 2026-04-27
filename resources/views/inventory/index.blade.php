<x-layouts.app :title="'Inventory'" :breadcrumb="'Inventory / Paper Stock'">
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold">Paper Stock</h2>
            <a href="{{ route('inventory.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Add Stock</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b border-slate-200 text-left text-slate-500">
                    <th class="pb-2">Paper Type</th><th class="pb-2">GSM</th><th class="pb-2">Sheet Size</th><th class="pb-2">Available Sheets</th><th class="pb-2">Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($stocks as $stock)
                    <tr class="border-b border-slate-100">
                        <td class="py-2">{{ $stock->paperType?->name }}</td>
                        <td class="py-2">{{ $stock->gsm }}</td>
                        <td class="py-2">{{ str($stock->sheet_size)->headline() }}</td>
                        <td class="py-2">{{ number_format($stock->available_sheets) }}</td>
                        <td class="py-2">
                            @if($stock->is_low_stock)
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Low Stock</span>
                            @else
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Healthy</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
