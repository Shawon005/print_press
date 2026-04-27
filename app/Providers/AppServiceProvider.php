<?php

namespace App\Providers;

use App\Models\Ctp;
use App\Models\DeliveryChallan;
use App\Models\JobOrder;
use App\Models\JobPayment;
use App\Models\PaperStock;
use App\Policies\CtpPolicy;
use App\Policies\DeliveryChallanPolicy;
use App\Policies\JobOrderPolicy;
use App\Policies\JobPaymentPolicy;
use App\Policies\PaperStockPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(JobOrder::class, JobOrderPolicy::class);
        Gate::policy(PaperStock::class, PaperStockPolicy::class);
        Gate::policy(JobPayment::class, JobPaymentPolicy::class);
        Gate::policy(Ctp::class, CtpPolicy::class);
        Gate::policy(DeliveryChallan::class, DeliveryChallanPolicy::class);
    }
}
