<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModuleRecordController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', [PortalController::class, 'home'])->name('portal.home');
    Route::get('/company-profile', [PortalController::class, 'companyProfile'])->name('company-profile.edit');
    Route::post('/settings/company-profile', [PortalController::class, 'updateCompanyProfile'])->name('settings.company-profile.update');

    Route::get('/modules/{module}/create', [ModuleRecordController::class, 'create'])->name('modules.create');
    Route::get('/modules/{module}/{id}', [ModuleRecordController::class, 'show'])->name('modules.show');
    Route::get('/modules/{module}/{id}/edit', [ModuleRecordController::class, 'edit'])->name('modules.edit');
    Route::get('/modules/{module}/{id}/print', [ModuleRecordController::class, 'print'])->name('modules.print');
    Route::post('/quotations/print-batch', [ModuleRecordController::class, 'printBatchQuotations'])->name('quotations.print-batch');
    Route::post('/modules/{module}', [ModuleRecordController::class, 'store'])->name('modules.store');
    Route::put('/modules/{module}/{id}', [ModuleRecordController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{module}/{id}', [ModuleRecordController::class, 'destroy'])->name('modules.destroy');
    Route::patch('/orders/{id}/status', [ModuleRecordController::class, 'updateOrderStatus'])->name('orders.status');
    Route::post('/orders/calc-preview', [ModuleRecordController::class, 'previewOrderCalculation'])->name('orders.calc-preview');
    Route::get('/invoices/job-orders/{id}/summary', [ModuleRecordController::class, 'invoiceJobOrderSummary'])->name('invoices.job-orders.summary');
    Route::get('/modules/{module}/export', [ModuleRecordController::class, 'export'])->name('modules.export');
    Route::get('/reports/customers/{customer}', [PortalController::class, 'customerReport'])->name('reports.customer');

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/{page}', [PortalController::class, 'show'])->name('portal.page');
});
