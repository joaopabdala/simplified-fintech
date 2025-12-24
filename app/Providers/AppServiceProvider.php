<?php

namespace App\Providers;

use App\Services\Authorization\AuthorizationInterface;
use App\Services\Authorization\AuthorizationProvider;
use App\Services\Notification\NotificationInterface;
use App\Services\Notification\NotificationProvider;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthorizationInterface::class, function ($app) {
            return AuthorizationProvider::make();
        });

        $this->app->bind(NotificationInterface::class, function ($app) {
            return NotificationProvider::make();
        });
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! Context::has('traceId')) {
            Context::add('traceId', (string) Str::uuid());
        }

        Telescope::tag(function (IncomingEntry $entry) {
            return ['traceId:'.Context::get('traceId')];
        });
    }
}
