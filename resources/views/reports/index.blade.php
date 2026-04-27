<x-layouts.app :title="'Reports'" :breadcrumb="'Reports / Dashboard'">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="mb-3 text-lg font-semibold">Monthly Revenue</h3>
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="mb-3 text-lg font-semibold">Job Status Breakdown</h3>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <h3 class="mb-3 text-lg font-semibold">Paper Consumption Summary</h3>
        <ul class="space-y-2 text-sm">
            @foreach($paperConsumption as $item)
                <li>{{ $item->stock?->paperType?->name }} - {{ number_format($item->consumed) }} sheets</li>
            @endforeach
        </ul>
    </div>

    <script>
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyRevenue->keys()->values()),
                datasets: [{ label: 'Revenue', data: @json($monthlyRevenue->values()), backgroundColor: '#2563eb' }]
            }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: @json($statusBreakdown->keys()->values()),
                datasets: [{ data: @json($statusBreakdown->values()), backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#ef4444', '#7c3aed'] }]
            }
        });
    </script>
</x-layouts.app>
