<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Support\ServiceProvider;

class LaravelCrudServiceProvider extends ServiceProvider
{
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
        $this->publishes([
            __DIR__.'/../config/courier.php' => config_path('crud-generator.php'),
        ]);

        $this->registerCommands();
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ControllerMakeCommand::class,
            CrudMakeCommand::class,
            ModelMakeCommand::class,
            RequestMakeCommand::class,
            ResourceMakeCommand::class,
        ]);
    }
}
