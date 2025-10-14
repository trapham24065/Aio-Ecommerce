<?php

namespace App\Providers;

use App\Models\Inventory;
use App\Models\Order;
use App\Observers\InventoryObserver;
use App\Observers\OrderObserver;
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
        Inventory::observe(InventoryObserver::class);
        Order::observe(OrderObserver::class);
    }

}
