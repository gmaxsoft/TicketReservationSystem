<?php

namespace App\Providers;

use App\Events\PaymentConfirmed;
use App\Listeners\GenerateTicketPdfAndEmail;
use App\Services\Facebook\FacebookConversionApiService;
use App\Services\Olx\EloquentAdvertRepository;
use App\Services\Olx\OlxService;
use App\Services\Przelewy24\Przelewy24Factory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EloquentAdvertRepository::class);
        $this->app->singleton(OlxService::class);
        $this->app->singleton(Przelewy24Factory::class);
        $this->app->singleton(FacebookConversionApiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(PaymentConfirmed::class, GenerateTicketPdfAndEmail::class);
    }
}
