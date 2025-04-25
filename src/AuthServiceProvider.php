<?php

namespace MuzhikiPro\Auth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/muzhiki-auth.php', 'muzhiki-auth');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/muzhiki-auth.php' => config_path('muzhiki-auth.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Пример гейта
        Gate::define('view-dashboard', function ($user) {
            return $user->hasPermission('view_dashboard');
        });
    }
}
