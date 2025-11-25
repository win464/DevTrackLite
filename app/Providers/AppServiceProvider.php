<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Project;
use App\Models\Milestone;
use App\Policies\ProjectPolicy;
use App\Policies\MilestonePolicy;

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

        // Register model policies (ProjectPolicy, MilestonePolicy) here so policies are available
        // without needing a dedicated AuthServiceProvider file in this app layout.
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Milestone::class, MilestonePolicy::class);
    }
}
