<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login | Printing Press Management System</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="flex min-h-screen items-center justify-center bg-[var(--app-bg)] px-4 py-10">
            <div class="grid w-full max-w-6xl overflow-hidden rounded-[36px] border border-white/80 bg-white/90 shadow-[0_30px_90px_rgba(15,23,42,0.12)] lg:grid-cols-[1.1fr_0.9fr]">
                <section class="hero-panel p-8 md:p-12">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">Printing Press SaaS ERP</p>
                    <h1 class="mt-5 text-4xl font-black tracking-tight text-slate-950 md:text-5xl">Manage quotation, production, finance, inventory, and delivery in one workspace.</h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-700">This project now includes a real ERP database foundation with seeded tenants, users, customers, suppliers, products, materials, orders, invoices, expenses, and deliveries.</p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[26px] bg-white/75 p-5 shadow-sm">
                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Demo Login</p>
                            <p class="mt-3 text-base font-bold text-slate-900">press-admin@example.com</p>
                            <p class="mt-1 text-sm text-slate-500">Password: `12345`</p>
                        </div>
                        <div class="rounded-[26px] bg-white/75 p-5 shadow-sm">
                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Modules</p>
                            <p class="mt-3 text-sm leading-7 text-slate-700">Customers, suppliers, products, raw materials, warehouses, quotations, orders, purchases, invoices, expenses, deliveries, reports, users, settings, and subscription.</p>
                        </div>
                    </div>
                </section>

                <section class="p-8 md:p-12">
                    <div class="mx-auto max-w-md">
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[var(--brand)]">Secure Access</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Sign in to your tenant workspace</h2>

                        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                            @csrf
                            <div>
                                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', 'press-admin@example.com') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none placeholder:text-slate-400">
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                                <input id="password" name="password" type="password" value="12345" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none placeholder:text-slate-400">
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <label class="flex items-center gap-3 text-sm text-slate-600">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[var(--brand)]">
                                Keep me signed in
                            </label>

                            <button type="submit" class="w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">Login to Dashboard</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
