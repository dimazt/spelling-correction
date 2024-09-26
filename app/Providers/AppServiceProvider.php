<?php

namespace App\Providers;

use App\Services\TextProcessingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(TextProcessingService::class, function ($app) {
            return new TextProcessingService();
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(TextProcessingService::class, function ($app) {
            return new TextProcessingService();
        });
    }
}
