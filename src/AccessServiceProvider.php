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

        Blade::directive('roles', function ($args) {
            return "<?php if(app(\Panoscape\Access\Access::class)->hasRoles($args)) :  ?>";
        });        
        Blade::directive('endroles', function () {
            return "<?php endif;  ?>";
        });

        Blade::directive('permissions', function ($args) {
            return "<?php if(app(\Panoscape\Access\Access::class)->hasPermissions($args)) :  ?>";
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