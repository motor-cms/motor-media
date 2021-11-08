<?php

namespace Motor\Media\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Motor\Media\Models\File;

/**
 * Class MotorServiceProvider
 *
 * @package Motor\Media\Providers
 */
class MotorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->routes();
        $this->routeModelBindings();
        $this->navigationItems();
        $this->permissions();
        $this->migrations();
        merge_local_config_with_db_configuration_variables('motor-media');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/motor-media.php', 'motor-media');
    }

    /**
     * Set migration path
     */
    public function migrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Merge permission config file
     */
    public function permissions()
    {
        $config = $this->app['config']->get('motor-admin-permissions', []);
        $this->app['config']->set('motor-admin-permissions', array_replace_recursive(require __DIR__.'/../../config/motor-admin-permissions.php', $config));
    }

    /**
     * Set routes
     */
    public function routes()
    {
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../../routes/api.php';
        }
    }

    /**
     * Add route model bindings
     */
    public function routeModelBindings()
    {
        Route::bind('file', static function ($id) {
            return File::findOrFail($id);
        });
    }

    /**
     * Merge backend navigation items from configuration file
     */
    public function navigationItems()
    {
        $config = $this->app['config']->get('motor-admin-navigation', []);
        $this->app['config']->set('motor-admin-navigation', array_replace_recursive(require __DIR__.'/../../config/motor-admin-navigation.php', $config));
    }
}
