<?php

namespace Motor\Media\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Motor\Media\Console\Commands\MediaSyncToS3sCommand;
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
        $this->config();
        $this->routes();
        $this->routeModelBindings();
        $this->translations();
        $this->views();
        $this->navigationItems();
        $this->permissions();
        $this->registerCommands();
        $this->migrations();
        $this->publishResourceAssets();
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
     * Set assets to be published
     */
    public function publishResourceAssets()
    {
        $assets = [
            __DIR__.'/../../public/plugins/jstree' => public_path('plugins/jstree'),
        ];

        $this->publishes($assets, 'motor-media-install');
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
        $config = $this->app['config']->get('motor-backend-permissions', []);
        $this->app['config']->set('motor-backend-permissions', array_replace_recursive(require __DIR__.'/../../config/motor-backend-permissions.php', $config));
    }

    /**
     * Register commands
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
            require __DIR__.'/../../routes/web.php';
            require __DIR__.'/../../routes/api.php';
        }
    }

    /**
     * Set configuration files for publishing
     */
    public function config()
    {
        //$this->publishes([
        //    __DIR__ . '/../../config/motor-backend-project.php'          => config_path('motor-backend-project.php'),
        //], 'motor-backend-install');
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
     * Set view path
     */
    public function views()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'motor-media');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/motor-media'),
        ], 'motor-media-views');
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
        $config = $this->app['config']->get('motor-backend-navigation', []);
        $this->app['config']->set('motor-backend-navigation', array_replace_recursive(require __DIR__.'/../../config/motor-backend-navigation.php', $config));
    }
}
