<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Foundation\Events\PublishingStubs;

class AddStubsToPublish
{
    public function handle(PublishingStubs $event): void
    {
        $stubs = [
            __DIR__.'/stubs/controller.crud.stub' => 'controller.crud.stub',
            __DIR__.'/stubs/model.crud.stub' => 'model.crud.stub',
            __DIR__.'/stubs/request.crud.stub' => 'request.crud.stub',
            __DIR__.'/stubs/resource-collection.crud.stub' => 'resource-collection.crud.stub',
            __DIR__.'/stubs/resource.crud.stub' => 'resource.crud.stub',
            __DIR__.'/stubs/route.crud.stub' => 'route.crud.stub',
        ];

        foreach ($stubs as $path => $name) {
            $event->add($path, $name);
        }
    }
}
