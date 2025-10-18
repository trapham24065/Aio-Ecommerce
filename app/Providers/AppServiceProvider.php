<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Inventory;
use App\Models\Order;
use App\Observers\InventoryObserver;
use App\Observers\OrderObserver;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use ApiPlatform\State\ProcessorInterface;
use App\ApiPlatform\State\EloquentPersistProcessor;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
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
