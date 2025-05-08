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
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        $this->publishes([
            __DIR__.'/config/muzhiki-auth.php' => config_path('muzhiki-auth.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/database/seeders/' => database_path('seeders/vendor/MuzhikiProAuth'),
        ], 'seeders');


        // Пример гейта
        Gate::define('view-dashboard', function ($user) {
            return $user->hasPermission('view_dashboard');
        });
    }
}
