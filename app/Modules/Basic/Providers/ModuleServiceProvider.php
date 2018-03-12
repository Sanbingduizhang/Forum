<?php

namespace App\Modules\Basic\Providers;

use App\Modules\Basic\Support\Helper;
use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'basic');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'basic');
//        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'basic');
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        Helper::loadModuleHelpers(__DIR__);

        /**
         * split_files_with_basename 确保已经被正确加载
         * basic.helper.file
         */
        $configs = split_files_with_basename($this->app['files']->glob(__DIR__ . '/../Config/*.php'));
        foreach ($configs as $key => $row)
        {
            $this->mergeConfigFrom($row, $key);
        }

        $this->app->register(RouteServiceProvider::class);
        $this->app->register(MiddlewareServiceProvider::class);
    }
}
