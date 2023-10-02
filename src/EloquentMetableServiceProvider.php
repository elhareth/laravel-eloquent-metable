<?php

namespace Elhareth\LaravelEloquentMetable;

use Illuminate\Support\ServiceProvider;

class EloquentMetableServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__) . '/database/migrations');
        Metable::observe(MetableObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
