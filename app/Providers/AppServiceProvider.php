<?php

namespace App\Providers;

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
        // Ensure route middleware aliases are registered (helps test/runtime alias resolution)
        $router = $this->app->make(\Illuminate\Routing\Router::class);

        // register aliases used in routes
        $router->aliasMiddleware('ability', \App\Http\Middleware\EnsureTokenHasAbility::class);
        $router->aliasMiddleware('role', \App\Http\Middleware\EnsureUserHasRole::class);
    }
}
