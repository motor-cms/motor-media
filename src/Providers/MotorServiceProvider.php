<?php

namespace Motor\Media\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Motor\Media\Console\Commands\MediaSyncToS3sCommand;
use Motor\Media\Console\Commands\CopyMedia;
use Motor\Media\Console\Commands\DeleteLocalMedia;
use Motor\Media\Console\Commands\MigrateMedia;
use Motor\Media\Models\File;

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
        $this->registerCommands();
        $this->migrations();
        merge_local_config_with_db_configuration_variables('motor-media');
        $this->commands([
            MigrateMedia::class,
            CopyMedia::class,
            DeleteLocalMedia::class,

        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/motor-media.php', 'motor-media');

        $config = $this->app['config']->get('scout', []);
        $this->app['config']->set('scout', array_merge_recursive(require __DIR__.'/../../config/scout.php', $config));
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
     * Register commands
     *
     * @return void
     */
    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MediaSyncToS3sCommand::class,
            ]);
        }
    }

    /**
     * Set routes
     */
    public function routes()
    {
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../../routes/api.php';
            require __DIR__.'/../../routes/web.php';
        }
    }

    /**
     * Set configuration files for publishing
     */
    public function config()
    {
        // $this->publishes([
        //    __DIR__ . '/../../config/motor-backend-project.php'          => config_path('motor-backend-project.php'),
        // ], 'motor-backend-install');
    }

    /**
     * Set translation path
     */
    public function translations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'motor-media');

        $this->publishes([
            __DIR__.'/../../lang' => resource_path('lang/vendor/motor-media'),
        ], 'motor-media-translations');
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
