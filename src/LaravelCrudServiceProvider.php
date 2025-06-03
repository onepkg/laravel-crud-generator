<?php

namespace OnePkg\LaravelCrudGenerator;

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
        $this->registerCommands();
    }

    private function registerCommands()
    {
        if (!$this->app->runningInConsole()) {
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
