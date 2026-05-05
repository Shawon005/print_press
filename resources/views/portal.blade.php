<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageMeta['label'] }} | Printing Press Management System</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        @php
            $L = function ($en, $bn) use ($locale) {
                return ($locale ?? 'en') === 'bn' ? $bn : $en;
            };
        @endphp
        

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
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">{{ $L('Printing Press', 'প্রিন্টিং প্রেস') }}</p>
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
                                        @case('printer')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 8V4h10v4" /><rect x="4" y="8" width="16" height="8" rx="2" /><path d="M7 16h10v4H7z" /></svg>
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
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-200">{{ $L('Blueprint Coverage', 'ব্লুপ্রিন্ট কাভারেজ') }}</p>
                        <h2 class="mt-3 text-2xl font-black leading-tight">{{ $L('From CRM to dispatch in one ERP workspace.', 'একটি ERP ওয়ার্কস্পেসে CRM থেকে ডেলিভারি পর্যন্ত সব কাজ।') }}</h2>
                        <p class="mt-3 text-sm text-slate-300">{{ $L('Tenant isolation, roles and permissions, finance visibility, supplier tracking, and production stages are all represented across this website.', 'টেন্যান্ট আলাদা রাখা, রোল ও পারমিশন, ফাইন্যান্স দেখা, সাপ্লায়ার ট্র্যাকিং এবং প্রোডাকশন ধাপ সবকিছু এই সিস্টেমে আছে।') }}</p>
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
                                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                                    <span class="text-xs font-semibold text-slate-500">{{ $ui['language'] }}</span>
                                    <select class="bg-transparent text-sm font-semibold text-slate-700 outline-none" onchange="if(this.value){ window.location.href=this.value; }">
                                        <option value="{{ route('portal.language', ['locale' => 'en']) }}" @selected($locale === 'en')>{{ $ui['english'] }}</option>
                                        <option value="{{ route('portal.language', ['locale' => 'bn']) }}" @selected($locale === 'bn')>{{ $ui['bangla'] }}</option>
                                    </select>
                                </div>
                                <button command="show-modal" commandfor="dialog" type="button"  class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Unit Converter</button>
                                <a href="{{ $workspace['company_profile_url'] }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">{{ $ui['company_profile'] }}</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">{{ $ui['logout'] }}</button>
                                </form>
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                                    @if (!empty($workspace['company_logo']))
                                        <img src="{{ $workspace['company_logo'] }}" alt="Company logo" class="h-11 w-11 rounded-full border border-slate-200 object-cover">
                                    @else
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-900 text-white">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4" /><path d="M5 20a7 7 0 0 1 14 0" /></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $workspace['user'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $workspace['company_name'] }}</p>
                                        <p class="text-xs text-slate-400">{{ $workspace['role'] }}</p>
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
                        @if (session('error'))
                            <div class="rounded-[24px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">
                                {{ session('error') }}
                            </div>
                        @endif
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
                        <section class="hero-panel overflow-hidden rounded-[32px] p-6 md:p-8 ">
                            <div class="grid gap-6  justify-end">
                                <div>
                                    <div class=" flex flex-wrap gap-3">
                                        @if (!empty($pageData['primary_action']))
                                            <a href="{{ $pageData['primary_action']['url'] }}" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $pageData['primary_action']['label'] }}</a>
                                        @endif
                                        @if (!empty($pageData['secondary_action']))
                                            <a href="{{ $pageData['secondary_action']['url'] }}" class="rounded-2xl border border-slate-300 bg-white/80 px-5 py-3 text-sm font-semibold text-slate-800">{{ $pageData['secondary_action']['label'] }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </section>

                      

                        @if (!empty($pageData['feature_cards']))
                            <section class="grid gap-6 xl:grid-cols-3">
                                @foreach ($pageData['feature_cards'] as $card)
                                    <article class="surface-card p-6">
                                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">{{ $L('Highlight', 'হাইলাইট') }}</p>
                                        <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ $card['title'] }}</h3>
                                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $card['text'] }}</p>
                                    </article>
                                @endforeach
                            </section>
                        @endif

                        @if ($currentPage === 'settings' && !empty($pageData['settings_tabs']))
                            <section class="grid gap-6" x-data="{ activeTab: '{{ $pageData['settings_tabs'][0]['key'] ?? 'paper-types' }}' }">
                                <article class="surface-card p-6">
                                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">{{ $L('Settings Masters', 'সেটিংস মাস্টার') }}</p>
                                            <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ $L('Paper, Ink, Sheet, Unit', 'পেপার, ইঙ্ক, শিট, ইউনিট') }}</h3>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($pageData['settings_tabs'] as $tab)
                                                <button type="button" @click="activeTab = '{{ $tab['key'] }}'" :class="activeTab === '{{ $tab['key'] }}' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="rounded-2xl px-4 py-2 text-sm font-semibold">{{ $tab['label'] }}</button>
                                            @endforeach
                                        </div>
                                    </div>

                                    @foreach ($pageData['settings_tabs'] as $tab)
                                        <div x-show="activeTab === '{{ $tab['key'] }}'" x-cloak>
                                            <div class="mb-4 flex justify-end">
                                                <a href="{{ $tab['create_url'] }}" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $L('Add', 'যোগ করুন') }} {{ $tab['label'] }}</a>
                                            </div>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full border-separate border-spacing-y-3 text-left">
                                                    <thead>
                                                        <tr class="text-sm font-semibold text-slate-500">
                                                            @foreach ($tab['columns'] as $column)
                                                                <th class="px-4">{{ $column }}</th>
                                                            @endforeach
                                                            <th class="px-4 text-right">{{ $L('Actions', 'অ্যাকশন') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($tab['rows'] as $row)
                                                            <tr class="bg-slate-50 text-sm text-slate-700 shadow-sm">
                                                                @foreach ($row['cells'] as $cell)
                                                                    <td class="px-4 py-4 {{ $loop->first ? 'rounded-l-2xl font-semibold text-slate-900' : '' }}">{{ $cell }}</td>
                                                                @endforeach
                                                                <td class="rounded-r-2xl px-4 py-4 text-right">
                                                                    <div class="flex flex-wrap justify-end gap-2">
                                                                        <a href="{{ route('modules.print', [$row['module'], $row['record_id']]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700" target="_blank">{{ $L('Print', 'প্রিন্ট') }}</a>
                                                                        <a href="{{ route('modules.edit', [$row['module'], $row['record_id']]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">{{ $L('Edit', 'এডিট') }}</a>
                                                                        <form method="POST" action="{{ route('modules.destroy', [$row['module'], $row['record_id']]) }}" onsubmit="return confirm('Delete this record?')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white">{{ $L('Delete', 'ডিলিট') }}</button>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </article>
                            </section>
                        @else
                        <section class="grid gap-6 ">
                            @if ($currentPage === 'reports' && !empty($pageData['report_filters']))
                                <article class="surface-card p-6">
                                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Report Filters</p>
                                            <h3 class="text-2xl font-black tracking-tight text-slate-900">সময়কাল এবং ব্যবধান নির্বাচন করুন</h3>
                                        </div>
                                        <form method="GET" action="{{ $pageData['report_filters']['submit_url'] }}" class="flex flex-wrap items-end gap-3">
                                            <div>
                                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Period</label>
                                                <select name="period" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                                                    <option value="monthly" @selected(($pageData['report_filters']['period'] ?? 'monthly') === 'monthly')>Monthly</option>
                                                    <option value="day" @selected(($pageData['report_filters']['period'] ?? 'monthly') === 'day')>Day</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Month</label>
                                                <input type="month" name="month" value="{{ $pageData['report_filters']['month'] ?? now()->format('Y-m') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                                            </div>
                                            <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $L('Apply Filter', 'ফিল্টার প্রয়োগ') }}</button>
                                        </form>
                                    </div>
                                </article>
                            @endif

                            @if ($currentPage === 'reports' && !empty($pageData['chart']))
                                <article class="surface-card p-6">
                                    <div class="mb-4">
                                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Financial Chart</p>
                                        <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ $pageData['chart']['title'] }}</h3>
                                    </div>
                                    <div class="h-[360px]">
                                        <canvas id="reports-financial-chart"></canvas>
                                    </div>
                                </article>
                            @endif

                            @if ($currentPage === 'printing-rectangle' && !empty($pageData['printing_calculator']))
                                <article class="surface-card p-6">
                                    <div class="mb-6">
                                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Rectangle System</p>
                                        <h3 class="text-2xl font-black tracking-tight text-slate-900">Page Fit by Orientation</h3>
                                    </div>
                                    <form method="GET" action="{{ route('portal.page', $currentPage) }}" class="grid gap-4 md:grid-cols-4">
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Print Width</span>
                                            <input type="number" step="0.01" min="0" name="print_width" value="{{ $pageData['printing_calculator']['inputs']['print_width'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Print Height</span>
                                            <input type="number" step="0.01" min="0" name="print_height" value="{{ $pageData['printing_calculator']['inputs']['print_height'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sheet Width</span>
                                            <input type="number" step="0.01" min="0" name="sheet_width" value="{{ $pageData['printing_calculator']['inputs']['sheet_width'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sheet Height</span>
                                            <input type="number" step="0.01" min="0" name="sheet_height" value="{{ $pageData['printing_calculator']['inputs']['sheet_height'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <div class="md:col-span-4">
                                            <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">Calculate</button>
                                        </div>
                                    </form>
                                    <div class="mt-8 grid gap-6 lg:grid-cols-2">
                                        @foreach (['vertical' => 'Vertical', 'horizontal' => 'Horizontal'] as $orientationKey => $orientationLabel)
                                            @php
                                                $layout = $pageData['printing_calculator'][$orientationKey];
                                                $cols = $layout['columns'];
                                                $rows = $layout['rows'];
                                                $svgWidth = 420.0;
                                                $svgHeight = 260.0;
                                                $sheetInputWidth = max((float) ($pageData['printing_calculator']['inputs']['sheet_width'] ?? 0), 0.0);
                                                $sheetInputHeight = max((float) ($pageData['printing_calculator']['inputs']['sheet_height'] ?? 0), 0.0);
                                                $printCellWidth = max((float) ($layout['cell_width'] ?? 0), 0.0);
                                                $printCellHeight = max((float) ($layout['cell_height'] ?? 0), 0.0);
                                                $scale = ($sheetInputWidth > 0 && $sheetInputHeight > 0) ? min($svgWidth / $sheetInputWidth, $svgHeight / $sheetInputHeight) : 0;
                                                $drawSheetWidth = $scale > 0 ? $sheetInputWidth * $scale : $svgWidth;
                                                $drawSheetHeight = $scale > 0 ? $sheetInputHeight * $scale : $svgHeight;
                                                $offsetX = ($svgWidth - $drawSheetWidth) / 2;
                                                $offsetY = ($svgHeight - $drawSheetHeight) / 2;
                                                $cellWidth = $printCellWidth * $scale;
                                                $cellHeight = $printCellHeight * $scale;
                                                $usedWidth = $cols > 0 ? $cellWidth * $cols : 0;
                                                $usedHeight = $rows > 0 ? $cellHeight * $rows : 0;
                                            @endphp
                                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="mb-3 flex items-center justify-between">
                                                    <h4 class="text-lg font-bold text-slate-900">{{ $orientationLabel }}</h4>
                                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ $layout['total'] }} pages</span>
                                                </div>
                                                <p class="text-sm text-slate-600">{{ $layout['columns'] }} columns × {{ $layout['rows'] }} rows</p>
                                                <p class="mb-3 text-sm text-rose-600">Wastage: {{ number_format($layout['wastage_area'], 2) }} sq unit ({{ number_format($layout['wastage_percent'], 2) }}%)</p>
                                                <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" class="w-full rounded-xl border border-slate-300 bg-white">
                                                    <rect x="0.5" y="0.5" width="{{ $svgWidth - 1 }}" height="{{ $svgHeight - 1 }}" fill="#ffffff" stroke="#334155" stroke-width="1" />
                                                    <rect x="{{ $offsetX }}" y="{{ $offsetY }}" width="{{ $drawSheetWidth }}" height="{{ $drawSheetHeight }}" fill="#ffffff" stroke="#334155" stroke-width="1" />
                                                    @if ($cols > 0 && $rows > 0 && $scale > 0)
                                                        @for ($r = 0; $r < $rows; $r++)
                                                            @for ($c = 0; $c < $cols; $c++)
                                                                <rect x="{{ $offsetX + ($c * $cellWidth) + 0.8 }}" y="{{ $offsetY + ($r * $cellHeight) + 0.8 }}" width="{{ max($cellWidth - 1.6, 0) }}" height="{{ max($cellHeight - 1.6, 0) }}" fill="{{ $orientationKey === 'vertical' ? '#dbeafe' : '#dcfce7' }}" stroke="{{ $orientationKey === 'vertical' ? '#2563eb' : '#16a34a' }}" stroke-width="0.8" />
                                                            @endfor
                                                        @endfor
                                                        @if ($usedWidth < $drawSheetWidth)
                                                            <rect x="{{ $offsetX + $usedWidth }}" y="{{ $offsetY }}" width="{{ $drawSheetWidth - $usedWidth }}" height="{{ $drawSheetHeight }}" fill="#fee2e2" opacity="0.75" />
                                                        @endif
                                                        @if ($usedHeight < $drawSheetHeight)
                                                            <rect x="{{ $offsetX }}" y="{{ $offsetY + $usedHeight }}" width="{{ min($usedWidth, $drawSheetWidth) }}" height="{{ $drawSheetHeight - $usedHeight }}" fill="#fecaca" opacity="0.65" />
                                                        @endif
                                                    @else
                                                        <rect x="{{ $offsetX }}" y="{{ $offsetY }}" width="{{ $drawSheetWidth }}" height="{{ $drawSheetHeight }}" fill="#fee2e2" opacity="0.75" />
                                                    @endif
                                                </svg>
                                                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.12em] text-rose-700">Red shaded area = wastage</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </article>
                            @endif

                            @if ($currentPage === 'printing' && !empty($pageData['printing_calculator']))
                                <article class="surface-card p-6">
                                    <div class="mb-6">
                                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Die-Cut</p>
                                        <h3 class="text-2xl font-black tracking-tight text-slate-900">Real Shape Planner</h3>
                                        <p class="mt-2 text-sm text-slate-500">Auto flat width model: <strong>(2 × body width) + (2 × side flap) + glue flap</strong></p>
                                    </div>
                                    <form id="die-generate-form" class="grid gap-4 md:grid-cols-4">
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Body Width (mm)</span>
                                            <input type="number" step="0.01" min="0.01" name="body_width_mm" value="60" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Body Height (mm)</span>
                                            <input type="number" step="0.01" min="0.01" name="body_height_mm" value="90" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Top Flap (mm)</span>
                                            <input type="number" step="0.01" min="0" name="top_flap_mm" value="20" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Bottom Flap (mm)</span>
                                            <input type="number" step="0.01" min="0" name="bottom_flap_mm" value="20" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Side Flap (mm)</span>
                                            <input type="number" step="0.01" min="0" name="side_flap_mm" value="16" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Glue Flap (mm)</span>
                                            <input type="number" step="0.01" min="0" name="glue_flap_mm" value="12" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Bleed (mm)</span>
                                            <input type="number" step="0.01" min="0" name="bleed_mm" value="3" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Gap (mm)</span>
                                            <input type="number" step="0.01" min="0" name="gap_mm" value="2.54" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sheet Width (mm)</span>
                                            <input type="number" step="0.01" min="1" name="sheet_width_mm" value="762" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sheet Height (mm)</span>
                                            <input type="number" step="0.01" min="1" name="sheet_height_mm" value="508" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </label>
                                        <div class="md:col-span-2 flex items-end gap-3">
                                            <button type="button" id="btn-generate-die" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">Generate Shape</button>
                                            <button type="button" id="btn-calculate-layout" class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20">Calculate Nesting</button>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Upload SVG Die-Line</label>
                                            <input type="file" id="die-svg-file" accept=".svg" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        </div>
                                    </form>
                                    <div class="mt-6 grid gap-6 lg:grid-cols-3">
                                        <div class="rounded-2xl border border-slate-200 p-4 lg:col-span-2">
                                            <div id="die-konva-stage" class="h-[440px] w-full rounded-xl border border-slate-300 bg-white"></div>
                                            <p class="mt-2 text-xs text-slate-500">Manual tools: drag objects directly, press <strong>R</strong> to rotate selected, <strong>D</strong> to duplicate selected.</p>
                                        </div>
                                        <div class="rounded-2xl border border-slate-200 p-4">
                                            <h4 class="text-lg font-bold text-slate-900">Nesting Output</h4>
                                            <dl class="mt-3 space-y-2 text-sm text-slate-700">
                                                <div class="flex justify-between"><dt>Boxes/Sheet</dt><dd id="die-box-count">-</dd></div>
                                                <div class="flex justify-between"><dt>Rendered Boxes</dt><dd id="die-rendered-count">-</dd></div>
                                                <div class="flex justify-between"><dt>Used Area</dt><dd id="die-used-area">-</dd></div>
                                                <div class="flex justify-between"><dt>Wastage Area</dt><dd id="die-wastage-area">-</dd></div>
                                                <div class="flex justify-between"><dt>Wastage %</dt><dd id="die-wastage-percent">-</dd></div>
                                                <div class="flex justify-between"><dt>Layout Mode</dt><dd id="die-layout-mode">-</dd></div>
                                            </dl>
                                            <div class="mt-4 flex flex-col gap-2">
                                                <a id="die-export-svg" href="#" target="_blank" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-center text-xs font-semibold text-slate-700">Export SVG</a>
                                                <a id="die-export-pdf" href="#" target="_blank" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-center text-xs font-semibold text-slate-700">Export PDF</a>
                                            </div>
                                            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                                                <table class="min-w-full text-xs">
                                                    <thead class="bg-slate-100 text-slate-600">
                                                        <tr>
                                                            <th class="px-2 py-2 text-left">Metric</th>
                                                            <th class="px-2 py-2 text-left">Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="die-metrics-table-body" class="bg-white text-slate-700">
                                                        <tr><td class="px-2 py-2">Boxes/Sheet</td><td class="px-2 py-2">-</td></tr>
                                                        <tr><td class="px-2 py-2">Rendered Boxes</td><td class="px-2 py-2">-</td></tr>
                                                        <tr><td class="px-2 py-2">Used Area</td><td class="px-2 py-2">-</td></tr>
                                                        <tr><td class="px-2 py-2">Wastage Area</td><td class="px-2 py-2">-</td></tr>
                                                        <tr><td class="px-2 py-2">Wastage %</td><td class="px-2 py-2">-</td></tr>
                                                        <tr><td class="px-2 py-2">Layout Mode</td><td class="px-2 py-2">-</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endif

                            <article class="surface-card p-6">
                                @if ($currentPage === 'quotations' && !empty($pageData['table']['record_ids']))
                                    <form id="quotation-batch-print-form" method="POST" action="{{ route('quotations.print-batch') }}">
                                        @csrf
                                    </form>
                                @endif
                                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Data View</p>
                                        <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ $pageData['table']['title'] }}</h3>
                                    </div>
                                    <div class="flex flex-col gap-3 sm:flex-row">
                                        @if ($currentPage === 'quotations' && !empty($pageData['table']['record_ids']))
                                            <button type="submit" form="quotation-batch-print-form" class="rounded-2xl bg-slate-950 px-5 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $L('Print Selected', 'নির্বাচিতগুলো প্রিন্ট') }}</button>
                                        @endif
                                        <input type="text" placeholder="Search records" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none placeholder:text-slate-400">
                                        @if (!empty($pageData['export_url']))
                                            <a href="{{ $pageData['export_url'] }}" class="rounded-2xl bg-emerald-600 px-5 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/20">{{ $L('Export to Excel', 'এক্সেলে এক্সপোর্ট') }}</a>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-6 overflow-x-auto">
                                    <table class="min-w-full border-separate border-spacing-y-3 text-left">
                                        <thead>
                                            <tr class="text-sm font-semibold text-slate-500">
                                                @if ($currentPage === 'quotations' && !empty($pageData['table']['record_ids']))
                                                    <th class="px-4">
                                                        <input id="quotation-select-all" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                                                    </th>
                                                @endif
                                                @foreach ($pageData['table']['columns'] as $column)
                                                    <th class="px-4">{{ $column }}</th>
                                                @endforeach
                                                @if (!empty($pageData['table']['record_ids']))
                                                    <th class="px-4 text-right">{{ $L('Actions', 'অ্যাকশন') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pageData['table']['rows'] as $rowIndex => $row)
                                                @php($rowModule = $pageData['table']['module_map'][$rowIndex] ?? ($pageData['table']['module'] ?? $currentPage))
                                                <tr class="bg-slate-50 text-sm text-slate-700 shadow-sm">
                                                    @if ($currentPage === 'quotations' && !empty($pageData['table']['record_ids']))
                                                        <td class="px-4 py-4">
                                                            <input type="checkbox" name="quotation_ids[]" value="{{ $pageData['table']['record_ids'][$rowIndex] }}" form="quotation-batch-print-form" class="quotation-select-item h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                                                        </td>
                                                    @endif
                                                    @foreach ($row as $cell)
                                                        <td class="px-4 py-4 {{ $loop->first ? 'rounded-l-2xl font-semibold text-slate-900' : '' }} {{ $loop->last ? 'rounded-r-2xl' : '' }}">
                                                            @if ($loop->last)
                                                                <span class="status-pill">{{ $cell }}</span>
                                                            @elseif (
                                                                $currentPage === 'orders'
                                                                && (($pageData['table']['columns'][$loop->index] ?? null) === 'Invoice Status')
                                                            )
                                                                <a href="{{ route('portal.page', 'invoices') }}" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-[var(--brand)] hover:bg-slate-100">
                                                                    {{ $cell }}
                                                                </a>
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
                                                                        <button type="submit" class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white">{{ $L('Update', 'আপডেট') }}</button>
                                                                    </form>
                                                                @endif
                                                                @if (in_array($rowModule, ['orders', 'quotations'], true))
                                                                    <a href="{{ route('modules.show', [$rowModule, $pageData['table']['record_ids'][$rowIndex]]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">{{ $L('View', 'দেখুন') }}</a>
                                                                @endif
                                                                @php($printableModules = ['customers', 'suppliers', 'products', 'raw-materials', 'warehouses', 'quotations', 'orders', 'purchases', 'invoices', 'deliveries', 'expenses', 'users', 'roles', 'paper-types', 'ink-types', 'standard-sheets', 'units'])
                                                                @if (in_array($rowModule, $printableModules, true))
                                                                    <a href="{{ route('modules.print', [$rowModule, $pageData['table']['record_ids'][$rowIndex]]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700" target="_blank">{{ $L('Print', 'প্রিন্ট') }}</a>
                                                                @endif
                                                                <a href="{{ route('modules.edit', [$rowModule, $pageData['table']['record_ids'][$rowIndex]]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">{{ $L('Edit', 'এডিট') }}</a>
                                                                <form method="POST" action="{{ route('modules.destroy', [$rowModule, $pageData['table']['record_ids'][$rowIndex]]) }}" onsubmit="return confirm('Delete this record?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white">{{ $L('Delete', 'ডিলিট') }}</button>
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
                                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">{{ !empty($pageData['secondary_table']['is_report']) ? 'Customer Analytics' : 'Role Access' }}</p>
                                            <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ $pageData['secondary_table']['title'] }}</h3>
                                        </div>
                                        @if (empty($pageData['secondary_table']['is_report']))
                                            <a href="{{ route('modules.create', $pageData['secondary_table']['module']) }}" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">Add Role</a>
                                        @endif
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="min-w-full border-separate border-spacing-y-3 text-left">
                                            <thead>
                                                <tr class="text-sm font-semibold text-slate-500">
                                                    @foreach ($pageData['secondary_table']['columns'] as $column)
                                                        <th class="px-4">{{ $column }}</th>
                                                    @endforeach
                                                    @if (empty($pageData['secondary_table']['is_report']))
                                                        <th class="px-4 text-right">Actions</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($pageData['secondary_table']['rows'] as $rowIndex => $row)
                                                    <tr class="bg-slate-50 text-sm text-slate-700 shadow-sm">
                                                        @foreach ($row as $cellIndex => $cell)
                                                            <td class="px-4 py-4 {{ $loop->first ? 'rounded-l-2xl font-semibold text-slate-900' : '' }}">
                                                                @if (!empty($pageData['secondary_table']['is_report']) && !empty($pageData['secondary_table']['action_urls']) && $cell === 'View Report' && !empty($pageData['secondary_table']['action_urls'][$rowIndex]))
                                                                    <a href="{{ $pageData['secondary_table']['action_urls'][$rowIndex] }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">View Report</a>
                                                                @else
                                                                    {{ $cell }}
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                        @if (empty($pageData['secondary_table']['is_report']))
                                                            <td class="rounded-r-2xl px-4 py-4 text-right">
                                                                <div class="flex flex-wrap justify-end gap-2">
                                                                    <a href="{{ route('modules.print', [$pageData['secondary_table']['module'], $pageData['secondary_table']['record_ids'][$rowIndex]]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700" target="_blank">Print</a>
                                                                    <a href="{{ route('modules.edit', [$pageData['secondary_table']['module'], $pageData['secondary_table']['record_ids'][$rowIndex]]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">Edit</a>
                                                                    <form method="POST" action="{{ route('modules.destroy', [$pageData['secondary_table']['module'], $pageData['secondary_table']['record_ids'][$rowIndex]]) }}" onsubmit="return confirm('Delete this role?')">
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
                        @endif
                    </div>
                </main>
            </div>
        </div>

<el-dialog>
  <dialog id="dialog" aria-labelledby="dialog-title" class="fixed inset-0  size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent" style="margin:auto; padding:20px; border-radius:16px;">
        <div id="unit-converter-modal"  class="fixed inset-0 z-[9999] bg-black/45">
            <div class="mx-auto mt-16 w-[94%] max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl" style="width:500px;">
                <div class="mb-4 flex items-center justify-between">
                    <h3  class="text-lg font-bold text-slate-900">Unit Converter</h3>
                    <button id="close-unit-converter" type="button"command="close" commandfor="dialog" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-600">Close</button>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-semibold text-slate-600">From</span>
                        <select id="converter-from" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <option value="in">Inch (in)</option>
                            <option value="mm">Millimeter (mm)</option>
                            <option value="cm">Centimeter (cm)</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-semibold text-slate-600">To</span>
                        <select id="converter-to" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <option value="mm">Millimeter (mm)</option>
                            <option value="in">Inch (in)</option>
                            <option value="cm">Centimeter (cm)</option>
                        </select>
                    </label>
                </div>
                <label class="mt-4 block">
                    <span class="mb-1 block text-sm font-semibold text-slate-600">Input Value</span>
                    <input id="converter-input" type="number" step="any" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Enter value">
                </label>
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase text-emerald-700">Result</p>
                    <p id="converter-output" class="text-lg font-bold text-emerald-800">-</p>
                </div>
            </div>
        </div>


  </dialog>
</el-dialog>
       


      
        @if ($currentPage === 'quotations')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const selectAll = document.getElementById('quotation-select-all');
                    const items = Array.from(document.querySelectorAll('.quotation-select-item'));
                    if (!selectAll || items.length === 0) {
                        return;
                    }

                    selectAll.addEventListener('change', function () {
                        items.forEach(function (item) {
                            item.checked = selectAll.checked;
                        });
                    });

                    items.forEach(function (item) {
                        item.addEventListener('change', function () {
                            selectAll.checked = items.every(function (current) {
                                return current.checked;
                            });
                        });
                    });
                });
            </script>
        @endif
        <script>
            function toggleUnitConverter(open) {
                const modal = document.getElementById('unit-converter-modal');
                if (!modal) return;
                const isOpen = modal.style.display === 'block';
                if (typeof open === 'boolean') {
                    modal.style.display = open ? 'block' : 'none';
                } else {
                    modal.style.display = isOpen ? 'none' : 'block';
                }
            }
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const converterModal = document.getElementById('unit-converter-modal');
                const converterOpen = document.getElementById('open-unit-converter');
                const converterClose = document.getElementById('close-unit-converter');
                const converterInput = document.getElementById('converter-input');
                const converterFrom = document.getElementById('converter-from');
                const converterTo = document.getElementById('converter-to');
                const converterOutput = document.getElementById('converter-output');

                function toMm(value, unit) {
                    if (unit === 'mm') return value;
                    if (unit === 'cm') return value * 10;
                    return value * 25.4;
                }

                function fromMm(value, unit) {
                    if (unit === 'mm') return value;
                    if (unit === 'cm') return value / 10;
                    return value / 25.4;
                }

                function runConversion() {
                    const raw = Number(converterInput.value);
                    if (!Number.isFinite(raw)) {
                        converterOutput.textContent = '-';
                        return;
                    }
                    const mm = toMm(raw, converterFrom.value);
                    const out = fromMm(mm, converterTo.value);
                    converterOutput.textContent = out.toFixed(4) + ' ' + converterTo.value;
                }

                if (converterOpen && converterClose && converterModal) {
                    converterModal.addEventListener('click', function (e) {
                        if (e.target === converterModal) toggleUnitConverter(false);
                    });
                }

                [converterInput, converterFrom, converterTo].forEach(function (el) {
                    if (el) el.addEventListener('input', runConversion);
                    if (el) el.addEventListener('change', runConversion);
                });
            });
        </script>
        @if ($currentPage === 'reports' && !empty($pageData['chart']))
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const canvas = document.getElementById('reports-financial-chart');
                    if (!canvas) return;

                    const chartData = @json($pageData['chart']);
                    new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Revenue',
                                    data: chartData.series.revenue,
                                    borderColor: '#0f766e',
                                    backgroundColor: 'rgba(15,118,110,0.12)',
                                    tension: 0.3,
                                    fill: false
                                },
                                {
                                    label: 'Expense',
                                    data: chartData.series.expense,
                                    borderColor: '#dc2626',
                                    backgroundColor: 'rgba(220,38,38,0.12)',
                                    tension: 0.3,
                                    fill: false
                                },
                                {
                                    label: 'Profit',
                                    data: chartData.series.profit,
                                    borderColor: '#2563eb',
                                    backgroundColor: 'rgba(37,99,235,0.12)',
                                    tension: 0.3,
                                    fill: false
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top' }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function (value) {
                                            return '৳' + Number(value).toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endif
        @if ($currentPage === 'printing' && !empty($pageData['printing_calculator']))
            <script src="https://cdn.jsdelivr.net/npm/konva@9/konva.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const api = @json($pageData['printing_calculator']['api']);
                    const csrf = '{{ csrf_token() }}';
                    let activeShapeId = null;
                    let selectedNode = null;
                    const stageWrap = document.getElementById('die-konva-stage');
                    if (!stageWrap) return;

                    const stage = new Konva.Stage({ container: 'die-konva-stage', width: stageWrap.clientWidth, height: 440 });
                    const layer = new Konva.Layer();
                    const tr = new Konva.Transformer({ rotateEnabled: true, enabledAnchors: [] });
                    layer.add(tr);
                    stage.add(layer);

                    function pointsToFlat(points) {
                        return points.flatMap(p => [p.x, p.y]);
                    }

                    function drawPlacements(layout) {
                        layer.destroyChildren();
                        layer.add(tr);
                        const sheetW = Number(layout.sheet_width_mm);
                        const sheetH = Number(layout.sheet_height_mm);
                        const pad = 12;
                        const scale = Math.min((stage.width() - (pad * 2)) / sheetW, (stage.height() - (pad * 2)) / sheetH);
                        const sheet = new Konva.Rect({
                            x: pad,
                            y: pad,
                            width: sheetW * scale,
                            height: sheetH * scale,
                            fill: '#fee2e2',
                            stroke: '#b91c1c',
                            strokeWidth: 1
                        });
                        layer.add(sheet);

                        let rendered = 0;
                        (layout.placements_json || []).forEach((p) => {
                            const pts = (p.points || []).map(pt => ({ x: pad + pt.x * scale, y: pad + pt.y * scale }));
                            const poly = new Konva.Line({
                                points: pointsToFlat(pts),
                                fill: '#dbeafe',
                                stroke: '#2563eb',
                                strokeWidth: 1.3,
                                closed: true,
                                draggable: true
                            });
                            poly.on('click', function() { selectedNode = poly; tr.nodes([poly]); });
                            layer.add(poly);
                            rendered += 1;
                        });
                        const renderedEl = document.getElementById('die-rendered-count');
                        if (renderedEl) renderedEl.textContent = String(rendered);
                        const tbody = document.getElementById('die-metrics-table-body');
                        if (tbody) {
                            const used = Number(layout.used_area_mm2).toFixed(2) + ' mm²';
                            const wastage = Number(layout.wastage_area_mm2).toFixed(2) + ' mm²';
                            const wastagePct = Number(layout.wastage_percent).toFixed(2) + '%';
                            tbody.innerHTML = '' +
                                '<tr><td class="px-2 py-2">Boxes/Sheet</td><td class="px-2 py-2">' + layout.box_count + '</td></tr>' +
                                '<tr><td class="px-2 py-2">Rendered Boxes</td><td class="px-2 py-2">' + rendered + '</td></tr>' +
                                '<tr><td class="px-2 py-2">Used Area</td><td class="px-2 py-2">' + used + '</td></tr>' +
                                '<tr><td class="px-2 py-2">Wastage Area</td><td class="px-2 py-2">' + wastage + '</td></tr>' +
                                '<tr><td class="px-2 py-2">Wastage %</td><td class="px-2 py-2">' + wastagePct + '</td></tr>' +
                                '<tr><td class="px-2 py-2">Layout Mode</td><td class="px-2 py-2">' + layout.layout_mode + '</td></tr>';
                        }
                        layer.draw();
                    }

                    async function post(url, body, isForm = false) {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: isForm ? { 'X-CSRF-TOKEN': csrf } : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: isForm ? body : JSON.stringify(body),
                        });
                        if (!response.ok) throw new Error('Request failed');
                        return response.json();
                    }

                    document.getElementById('btn-generate-die').addEventListener('click', async function () {
                        const fd = new FormData(document.getElementById('die-generate-form'));
                        const fileInput = document.getElementById('die-svg-file');
                        if (fileInput.files.length > 0) {
                            const uploadData = new FormData();
                            uploadData.append('die_svg', fileInput.files[0]);
                            uploadData.append('name', 'Uploaded Die');
                            const upload = await post(api.upload_svg, uploadData, true);
                            activeShapeId = upload.shape.id;
                            return;
                        }
                        const payload = Object.fromEntries(fd.entries());
                        const res = await post(api.generate_shape, payload);
                        activeShapeId = res.shape.id;
                    });

                    document.getElementById('btn-calculate-layout').addEventListener('click', async function () {
                        if (!activeShapeId) return alert('Generate or upload a die shape first.');
                        const fd = new FormData(document.getElementById('die-generate-form'));
                        const payload = {
                            die_shape_id: activeShapeId,
                            sheet_width_mm: Number(fd.get('sheet_width_mm')),
                            sheet_height_mm: Number(fd.get('sheet_height_mm')),
                            gap_mm: Number(fd.get('gap_mm')),
                            allow_mirror: true
                        };
                        const res = await post(api.calculate_layout, payload);
                        const l = res.layout;
                        document.getElementById('die-box-count').textContent = l.box_count;
                        document.getElementById('die-used-area').textContent = Number(l.used_area_mm2).toFixed(2) + ' mm²';
                        document.getElementById('die-wastage-area').textContent = Number(l.wastage_area_mm2).toFixed(2) + ' mm²';
                        document.getElementById('die-wastage-percent').textContent = Number(l.wastage_percent).toFixed(2) + '%';
                        document.getElementById('die-layout-mode').textContent = l.layout_mode;
                        document.getElementById('die-export-svg').href = res.export_svg_url;
                        document.getElementById('die-export-pdf').href = res.export_pdf_url;
                        drawPlacements(l);
                    });

                    document.addEventListener('keydown', function (e) {
                        if (!selectedNode) return;
                        if (e.key.toLowerCase() === 'r') {
                            const pts = selectedNode.points();
                            if (!pts || pts.length < 6) return;
                            let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                            for (let i = 0; i < pts.length; i += 2) {
                                const x = pts[i];
                                const y = pts[i + 1];
                                if (x < minX) minX = x;
                                if (x > maxX) maxX = x;
                                if (y < minY) minY = y;
                                if (y > maxY) maxY = y;
                            }
                            const cx = (minX + maxX) / 2;
                            const cy = (minY + maxY) / 2;
                            const rotated = [];
                            for (let i = 0; i < pts.length; i += 2) {
                                const x = pts[i];
                                const y = pts[i + 1];
                                const rx = cx - (y - cy);
                                const ry = cy + (x - cx);
                                rotated.push(rx, ry);
                            }
                            selectedNode.points(rotated);
                            selectedNode.rotation(0);
                            layer.draw();
                        }
                        if (e.key.toLowerCase() === 'd') {
                            const clone = selectedNode.clone({ x: selectedNode.x() + 10, y: selectedNode.y() + 10 });
                            clone.on('click', function() { selectedNode = clone; tr.nodes([clone]); });
                            layer.add(clone);
                            layer.draw();
                        }
                    });
                });
            </script>
        @endif
    </body>
</html>
