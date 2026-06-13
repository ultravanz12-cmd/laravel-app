<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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
        // Fix for older MySQL/Postgres string limits
        Schema::defaultStringLength(191);

        // Auto-run migrations on Render (FREE PLAN workaround)
        try {
            if (app()->environment('production')) {
                Artisan::call('migrate', [
                    '--force' => true,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Auto migration failed: ' . $e->getMessage());
        }
    }
}