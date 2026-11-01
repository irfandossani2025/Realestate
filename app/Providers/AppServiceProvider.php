<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\ValuationRequest;
use App\Observers\AuditableObserver;
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
        foreach ([Property::class, Lead::class, Lease::class, ValuationRequest::class, PropertyTransaction::class, Payment::class, Invoice::class, MaintenanceRequest::class, Document::class] as $model) {
            $model::observe(AuditableObserver::class);
        }
    }
}
