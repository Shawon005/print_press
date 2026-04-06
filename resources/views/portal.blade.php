<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageMeta['label'] }} | Printing Press Management System</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="min-h-screen bg-[var(--app-bg)] text-slate-900">
            <div class="app-shell mx-auto min-h-screen max-w-[1680px]">
                <aside class="app-sidebar border-r border-slate-200 bg-white/92 px-5 py-6 backdrop-blur">
                    <a href="{{ route('portal.home') }}" class="mb-8 flex items-center gap-3">
                        <div class="flex h-13 w-13 items-center justify-center rounded-2xl bg-[var(--brand-soft)] text-[var(--brand)] shadow-sm">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M7 17V7h10M7 7l10 10" />
                                <path d="M5 19h14" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">SaaS ERP</p>
                            <h1 class="text-xl font-black tracking-tight text-slate-900">{{ $workspace['name'] }}</h1>
                        </div>
                    </a>

                    <nav class="space-y-1.5">
                        @foreach ($pages as $key => $item)
                            <a href="{{ $key === 'dashboard' ? route('portal.home') : route('portal.page', $key) }}" class="sidebar-link {{ $currentPage === $key ? 'is-active' : '' }}">
                                <span class="sidebar-icon">
                                    @switch($item['icon'])
                                        @case('home')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12 12 4l9 8" /><path d="M5 10v10h14V10" /></svg>
                                            @break
                                        @case('users')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" /><circle cx="9.5" cy="7" r="3" /><path d="M20 8v6M17 11h6" /></svg>
                                            @break
                                        @case('building')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20h16" /><path d="M6 20V8l6-4 6 4v12" /><path d="M9 12h.01M15 12h.01M9 16h.01M15 16h.01" /></svg>
                                            @break
                                        @case('box')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4 7l8 4 8-4-8-4Z" /><path d="M4 7v10l8 4 8-4V7" /></svg>
                                            @break
                                        @case('layers')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3-9 4.5 9 4.5 9-4.5L12 3Z" /><path d="m3 12.5 9 4.5 9-4.5" /><path d="m3 17 9 4 9-4" /></svg>
                                            @break
                                        @case('grid')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16" /><path d="M7 4v16M17 4v16" /></svg>
                                            @break
                                        @case('quote')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 8h10M7 12h7M7 16h5" /><rect x="4" y="4" width="16" height="16" rx="2" /></svg>
                                            @break
                                        @case('clipboard')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="4" width="12" height="16" rx="2" /><path d="M9 4.5h6M9 9h6M9 13h6" /></svg>
                                            @break
                                        @case('cart')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="19" r="1.5" /><circle cx="17" cy="19" r="1.5" /><path d="M3 5h2l2.4 9.2a1 1 0 0 0 1 .8h8.8a1 1 0 0 0 1-.8L21 8H7" /></svg>
                                            @break
                                        @case('invoice')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h8l4 4v14H5V3h2Z" /><path d="M13 3v5h5M8 13h8M8 17h6" /></svg>
                                            @break
                                        @case('wallet')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" /><path d="M16 12h4" /><circle cx="16" cy="12" r="1" /></svg>
                                            @break
                                        @case('truck')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 16V6h11v10" /><path d="M14 10h3l4 4v2h-7" /><circle cx="7.5" cy="17.5" r="1.5" /><circle cx="17.5" cy="17.5" r="1.5" /></svg>
                                            @break
                                        @case('chart')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16" /><path d="M7 15V9M12 15V5M17 15v-3" /></svg>
                                            @break
                                        @case('shield')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v6c0 5 3.5 8 7 9 3.5-1 7-4 7-9V6l-7-3Z" /><path d="m9.5 12 1.8 1.8 3.2-3.6" /></svg>
                                            @break
                                        @case('settings')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v3M12 18v3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M3 12h3M18 12h3M4.9 19.1 7 17M17 7l2.1-2.1" /><circle cx="12" cy="12" r="4" /></svg>
                                            @break
                                        @default
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M3 12h18" /></svg>
                                    @endswitch
                                </span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-auto rounded-[28px] bg-slate-950 px-5 py-6 text-white shadow-[0_24px_80px_rgba(15,23,42,0.28)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-200">Blueprint Coverage</p>
                        <h2 class="mt-3 text-2xl font-black leading-tight">From CRM to dispatch in one ERP workspace.</h2>
                        <p class="mt-3 text-sm text-slate-300">Tenant isolation, roles and permissions, finance visibility, supplier tracking, and production stages are all represented across this website.</p>
                    </div>
                </aside>

                <main class="min-w-0">
                    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/88 backdrop-blur">
                        <div class="flex flex-col gap-4 px-5 py-4 md:flex-row md:items-center md:justify-between md:px-8">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">{{ $pageData['eyebrow'] }}</p>
                                <h2 class="text-2xl font-black tracking-tight text-slate-900">{{ $pageMeta['label'] }}</h2>
                                <p class="text-sm text-slate-500">{{ $pageMeta['title'] }}</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <button class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    {{ $workspace['status'] }}
                                </button>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Logout</button>
                                </form>
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-900 text-white">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4" /><path d="M5 20a7 7 0 0 1 14 0" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $workspace['user'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $workspace['role'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div class="space-y-6 px-5 py-6 md:px-8">
                        @if (session('success'))
                            <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        <section class="hero-panel overflow-hidden rounded-[32px] p-6 md:p-8">
                            <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
                                <div>
                                    <h3 class="max-w-4xl text-4xl font-black leading-tight tracking-tight text-slate-950 md:text-5xl">{{ $pageData['headline'] }}</h3>
                                    <p class="mt-5 max-w-3xl text-base leading-7 text-slate-700">{{ $pageData['description'] }}</p>
                                    <div class="mt-8 flex flex-wrap gap-3">
                                        @if (!empty($pageData['primary_action']))
                                            <a href="{{ $pageData['primary_action']['url'] }}" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $pageData['primary_action']['label'] }}</a>
                                        @endif
                                        @if (!empty($pageData['secondary_action']))
                                            <a href="{{ $pageData['secondary_action']['url'] }}" class="rounded-2xl border border-slate-300 bg-white/80 px-5 py-3 text-sm font-semibold text-slate-800">{{ $pageData['secondary_action']['label'] }}</a>
                                        @endif
                                    </div>
                                </div>

                                <div class="surface-card p-5">
                                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Workspace Focus</p>
                                    <div class="mt-5 space-y-3">
                                        @foreach (array_slice($pageData['stats'], 0, 3) as $stat)
                                            <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $stat['label'] }}</p>
                                                <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $stat['value'] }}</p>
                                                <p class="mt-1 text-sm text-slate-500">{{ $stat['note'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="grid gap-4 xl:grid-cols-4">
                            @foreach ($pageData['stats'] as $stat)
                                <article class="metric-card">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-5xl font-black tracking-tight text-slate-700">{{ $stat['value'] }}</p>
                                            <h4 class="mt-3 text-xl font-bold tracking-tight text-[var(--brand)]">{{ $stat['label'] }}</h4>
                                            <p class="mt-2 text-sm text-slate-500">{{ $stat['note'] }}</p>
                                        </div>
                                        <span class="metric-icon">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19h16" /><path d="M7 15V9M12 15V5M17 15v-3" /></svg>
                                        </span>
                                    </div>
                                </article>
                            @endforeach
                        </section>

                        @if (!empty($pageData['feature_cards']))
                            <section class="grid gap-6 xl:grid-cols-3">
                                @foreach ($pageData['feature_cards'] as $card)
                                    <article class="surface-card p-6">
                                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Highlight</p>
                                        <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ $card['title'] }}</h3>
                                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $card['text'] }}</p>
                                    </article>
                                @endforeach
                            </section>
                        @endif

                        <section class="grid gap-6 ">
                            <article class="surface-card p-6">
                                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Data View</p>
                                        <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ $pageData['table']['title'] }}</h3>
                                    </div>
                                    <div class="flex flex-col gap-3 sm:flex-row">
                                        <input type="text" placeholder="Search records" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none placeholder:text-slate-400">
                                        @if (!empty($pageData['export_url']))
                                            <a href="{{ $pageData['export_url'] }}" class="rounded-2xl bg-emerald-600 px-5 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/20">Export to Excel</a>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-6 overflow-x-auto">
                                    <table class="min-w-full border-separate border-spacing-y-3 text-left">
                                        <thead>
                                            <tr class="text-sm font-semibold text-slate-500">
                                                @foreach ($pageData['table']['columns'] as $column)
                                                    <th class="px-4">{{ $column }}</th>
                                                @endforeach
                                                @if (!empty($pageData['table']['record_ids']))
                                                    <th class="px-4 text-right">Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pageData['table']['rows'] as $rowIndex => $row)
                                                <tr class="bg-slate-50 text-sm text-slate-700 shadow-sm">
                                                    @foreach ($row as $cell)
                                                        <td class="px-4 py-4 {{ $loop->first ? 'rounded-l-2xl font-semibold text-slate-900' : '' }} {{ $loop->last ? 'rounded-r-2xl' : '' }}">
                                                            @if ($loop->last)
                                                                <span class="status-pill">{{ $cell }}</span>
                                                            @else
                                                                {{ $cell }}
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    @if (!empty($pageData['table']['record_ids']))
                                                        <td class="px-4 py-4 text-right">
                                                            <div class="flex flex-wrap justify-end gap-2">
                                                                @if ($currentPage === 'orders' && !empty($pageData['table']['status_options']))
                                                                    <form method="POST" action="{{ route('orders.status', $pageData['table']['record_ids'][$rowIndex]) }}" class="flex items-center gap-2">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">
                                                                            @foreach ($pageData['table']['status_options'] as $statusOption)
                                                                                <option value="{{ $statusOption }}" @selected(($pageData['table']['status_values'][$rowIndex] ?? null) === $statusOption)>{{ str($statusOption)->headline() }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <button type="submit" class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Update</button>
                                                                    </form>
                                                                @endif
                                                                <a href="{{ route('modules.edit', [$pageData['table']['module'] ?? $currentPage, $pageData['table']['record_ids'][$rowIndex]]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">Edit</a>
                                                                <form method="POST" action="{{ route('modules.destroy', [$pageData['table']['module'] ?? $currentPage, $pageData['table']['record_ids'][$rowIndex]]) }}" onsubmit="return confirm('Delete this record?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white">Delete</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            @if (!empty($pageData['secondary_table']))
                                <article class="surface-card p-6">
                                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Role Access</p>
                                            <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ $pageData['secondary_table']['title'] }}</h3>
                                        </div>
                                        <a href="{{ route('modules.create', $pageData['secondary_table']['module']) }}" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">Add Role</a>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="min-w-full border-separate border-spacing-y-3 text-left">
                                            <thead>
                                                <tr class="text-sm font-semibold text-slate-500">
                                                    @foreach ($pageData['secondary_table']['columns'] as $column)
                                                        <th class="px-4">{{ $column }}</th>
                                                    @endforeach
                                                    <th class="px-4 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($pageData['secondary_table']['rows'] as $rowIndex => $row)
                                                    <tr class="bg-slate-50 text-sm text-slate-700 shadow-sm">
                                                        @foreach ($row as $cell)
                                                            <td class="px-4 py-4 {{ $loop->first ? 'rounded-l-2xl font-semibold text-slate-900' : '' }}">{{ $cell }}</td>
                                                        @endforeach
                                                        <td class="rounded-r-2xl px-4 py-4 text-right">
                                                            <div class="flex flex-wrap justify-end gap-2">
                                                                <a href="{{ route('modules.edit', [$pageData['secondary_table']['module'], $pageData['secondary_table']['record_ids'][$rowIndex]]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">Edit</a>
                                                                <form method="POST" action="{{ route('modules.destroy', [$pageData['secondary_table']['module'], $pageData['secondary_table']['record_ids'][$rowIndex]]) }}" onsubmit="return confirm('Delete this role?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white">Delete</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </article>
                            @endif

                            <!-- <div class="space-y-6">
                                <article class="surface-card p-6">
                                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Module Scope</p>
                                    <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ $pageData['side_panel']['title'] }}</h3>
                                    <div class="mt-6 flex flex-wrap gap-3">
                                        @foreach ($pageData['side_panel']['items'] as $item)
                                            <span class="rounded-2xl bg-[var(--brand-soft)] px-4 py-3 text-sm font-semibold text-[var(--brand-deep)]">{{ $item }}</span>
                                        @endforeach
                                    </div>
                                </article>

                                <article class="surface-card p-6">
                                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Why This Matters</p>
                                    <h3 class="text-2xl font-black tracking-tight text-slate-900">Blueprint-aligned page design</h3>
                                    <p class="mt-4 text-sm leading-7 text-slate-600">This page is one part of the wider Printing Press Management System and follows the role, module, reporting, and workflow structure from your original prompt rather than a generic admin dashboard.</p>
                                </article>
                            </div> -->
                        </section>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
