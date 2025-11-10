<?php

namespace Motor\Media\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Motor\Media\Console\Commands\CopyMedia;
use Motor\Media\Console\Commands\DeleteLocalMedia;
use Motor\Media\Console\Commands\MigrateMedia;
use Motor\Media\Models\File;
use Storage;

/**
 * Class MotorServiceProvider
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
        $this->commands([
            MigrateMedia::class,
            CopyMedia::class,
            DeleteLocalMedia::class,
        ]);

        // Check s3 connectivity
        if (config('filesystems.disks.media-s3.bucket') !== null) {
            try {
                Storage::disk('media-s3');
                $this->app['config']->set('filesystems.has_s3', true);
            } catch (\Exception $e) {
                $this->app['config']->set('filesystems.has_s3', false);
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/motor-media.php', 'motor-media');

        if (! app()->configurationIsCached()) {
            $config = $this->app['config']->get('scout', []);
            $this->app['config']->set('scout', array_merge_recursive(require __DIR__.'/../../config/scout.php', $config));
        }
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
        $this->app['config']->set('motor-admin-permissions', array_merge_recursive(require __DIR__.'/../../config/motor-admin-permissions.php', $config));
    }

    /**
     * Set routes
     */
    public function routes()
    {
        Route::middleware('api')->prefix('api')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        });
        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        });
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
