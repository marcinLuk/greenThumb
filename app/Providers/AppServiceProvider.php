<?php

namespace App\Providers;

use App\Models\JournalEntry;
use App\Observers\JournalEntryObserver;
use App\Services\OpenRouter\OpenRouterService;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenRouterService::class, function ($app) {
            return new OpenRouterService(
                apiKey: config('services.openrouter.api_key'),
                baseUrl: config('services.openrouter.base_url'),
                logger: $app->make(LoggerInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JournalEntry::observe(JournalEntryObserver::class);
    }
}
