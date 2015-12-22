<?php

namespace Yajra\Acl;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Yajra\Acl\Models\Permission;

class AclServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->publishConfig();

        $this->publishMigrations();

        $this->registerPolicies($gate);

        $this->registerPermissions($gate);
    }

    /**
     * Publish package config file.
     */
    private function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
        ], 'laravel-acl');
    }

    /**
     * Publish package migration files.
     */
    private function publishMigrations()
    {
        $this->publishes([
            __DIR__ . '/../migrations/' => database_path('migrations'),
        ], 'laravel-acl');
    }

    /**
     * Register defined permissions from database.
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate
     */
    private function registerPermissions(GateContract $gate)
    {
        // Ignore permissions when running in console.
        if ($this->app->runningInConsole()) {
            return;
        }

        foreach ($this->getPermissions() as $permission) {
            $gate->define($permission->slug, function ($user) use ($permission) {
                return $user->hasRole($permission->roles);
            });
        }
    }

    /**
     * Get lists of permissions.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    private function getPermissions()
    {
        return Permission::with('roles')->get();
    }
}