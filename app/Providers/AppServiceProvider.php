<?php

namespace App\Providers;

use App\Helpers\InstallationHelper;
use App\Livewire\ServicePlanner;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Livewire\WorshipPlanRosterPreview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
        if (Schema::hasTable('filament_settings')){
            Config::set('app.name',setting('app_name','Connexion'));
        } else {
            Config::set('app.name','Connexion');
        }
        //Livewire::component('service-planner', ServicePlanner::class); 
        Livewire::component('worship-plan-roster-preview', WorshipPlanRosterPreview::class);
        Model::automaticallyEagerLoadRelationships();
        Relation::morphMap([
            //'book' => 'Bishopm\Church\Models\Book',
            //'course' => 'Bishopm\Church\Models\Course',
            //'event' => 'Bishopm\Church\Models\Event',
            //'group' => 'Bishopm\Church\Models\Group',
            //'household' => 'Bishopm\Church\Models\Household',
            //'individual' => 'Bishopm\Church\Models\Individual',
            //'pastoralcase' => 'Bishopm\Church\Models\Pastoralcase',
            //'post' => 'Bishopm\Church\Models\Post',
            //'prayer' => 'Bishopm\Church\Models\Prayer',
            //'project' => 'Bishopm\Church\Models\Project',
            //'sermon' => 'Bishopm\Church\Models\Service',
            //'song' => 'Bishopm\Church\Models\Song',
            //'task' => 'Bishopm\Church\Models\Task',
            'tenant' => 'App\Models\Tenant',
            'venue' => 'App\Models\Venue',
            //'video' => 'Bishopm\Church\Models\Video'
        ]);
    }
}