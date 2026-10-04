<?php

namespace App\Providers;

use App\Models\CounsellingRecord;
use App\Models\Referral;
use App\Observers\CounsellingRecordObserver;
use App\Observers\ReferralObserver;
use Illuminate\Pagination\Paginator;
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
        CounsellingRecord::observe(CounsellingRecordObserver::class);
        Referral::observe(ReferralObserver::class);

        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');
    }
}
