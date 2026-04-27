<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Printing Press ERP' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 w-72 border-r border-slate-200 bg-white p-6 transition-transform lg:static lg:translate-x-0">
        <div class="mb-8">
            <h1 class="text-xl font-bold">Printing Press ERP</h1>
            <p class="text-sm text-slate-500">Dhaka Wholesale Workflow</p>
        </div>
        <nav class="space-y-2 text-sm font-medium">
            <a href="{{ route('job-orders.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Job Orders</a>
            <a href="{{ route('inventory.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Inventory</a>
            <a href="{{ route('financials.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Financials</a>
            <a href="{{ route('purchase-orders.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Purchase Orders</a>
            <a href="{{ route('delivery-challans.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Delivery Challans</a>
            <a href="{{ route('ctps.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">CTP Plates</a>
            <a href="{{ route('reports.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Reports</a>
        </nav>
    </aside>

    <main class="min-h-screen">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:px-8">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="rounded-md border border-slate-300 px-2 py-1 text-sm lg:hidden">Menu</button>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Breadcrumb</p>
                    <p class="font-semibold">{{ $breadcrumb ?? 'Dashboard' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Low Stock Alerts</span>
                <div class="text-right">
                    <p class="text-sm font-semibold">{{ auth()->user()?->name }}</p>
                    <p class="text-xs text-slate-500">{{ auth()->user()?->email }}</p>
                </div>
            </div>
        </header>

        <section class="p-4 lg:p-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc pl-6">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </section>
    </main>
</div>
</body>
</html>
