<?php

namespace Panoscape\Access;

use Illuminate\Support\ServiceProvider;
use Blade;

class AccessServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations')
        ], 'migrations');

        $this->app->singleton(Access::class, function ($app) {
            return new Access;
        });

        Blade::directive('roles', function ($roles) {
            if(is_array($roles)) {
                $column = 'name';
                if(count($roles) >= 3) {                    
                    $column = $roles[2];
                }
                $requireAll = true;
                if(count($roles) >= 2) {
                    $requireAll = $roles[1];
                }           
                $roles = $roles[0];
                return "<?php if(app(\Panoscape\Access\Access::class)->hasRoles($roles,$requireAll,$column)) :  ?>";
            }
            return "<?php if(app(\Panoscape\Access\Access::class)->hasRoles($roles)) :  ?>";
        });        
        Blade::directive('endroles', function () {
            return "<?php endif;  ?>";
        });

        Blade::directive('permissions', function ($permissions) {
            if(is_array($permissions)) {
                $column = 'name';
                if(count($permissions) >= 3) {                    
                    $column = $permissions[2];
                }
                $requireAll = true;
                if(count($permissions) >= 2) {
                    $requireAll = $permissions[1];
                }           
                $permissions = $permissions[0];
                return "<?php if(app(\Panoscape\Access\Access::class)->hasPermissions($permissions,$requireAll,$column)) :  ?>";
            }
            return "<?php if(app(\Panoscape\Access\Access::class)->hasPermissions($permissions)) :  ?>";
        });        
        Blade::directive('endpermissions', function () {
            return "<?php endif;  ?>";
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}