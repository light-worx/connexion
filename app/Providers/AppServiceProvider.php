<?php

namespace App\Providers;

use App\Helpers\InstallationHelper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    use InstallationHelper;

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
        /*Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });*/
        if (Schema::hasTable('filament_settings')){
            Config::set('app.name',setting('app_name','Connexion'));
        } else {
            Config::set('app.name','Connexion');
        }
    }
}