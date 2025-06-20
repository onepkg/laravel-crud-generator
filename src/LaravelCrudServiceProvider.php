<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Foundation\Events\PublishingStubs;
use Illuminate\Support\Facades\Event;
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

        if (class_exists(PublishingStubs::class)) {
            Event::listen(PublishingStubs::class, AddStubsToPublish::class);
        }

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
            RequestJsonBodyMakeCommand::class,
        ]);
    }
}
