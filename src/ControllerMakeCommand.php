<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Routing\Console\ControllerMakeCommand as ConsoleControllerMakeCommand;
use Illuminate\Support\Str;

class ControllerMakeCommand extends ConsoleControllerMakeCommand
{
    use CommandHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'onepkg:make-controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/controller.crud.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }

    /**
     * @param  array<string, string>  $replace
     * @return array<string, string>
     */
    protected function buildModelReplacements(array $replace): array
    {
        $modelClass = $this->parseModel($this->getStringValue($this->option('model')));

        $requestReplace = $this->buildFormRequestReplacements($replace, $modelClass);
        $resourceReplace = $this->buildResourceReplacements($replace, $modelClass);

        return array_merge($requestReplace, $resourceReplace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ]);
    }

    /**
     * @param  array<string, string>  $replace
     * @param  string  $modelClass
     * @return array<string, string>
     */
    protected function buildFormRequestReplacements(array $replace, $modelClass): array
    {
        $namespace = $this->getNamespaceWith('App\\Http\\Requests');

        $classBasename = class_basename($modelClass);
        $listingRequestClass = $classBasename.'ListingRequest';
        $storeRequestClass = $classBasename.'StoreRequest';
        $updateRequestClass = $classBasename.'UpdateRequest';

        return array_merge($replace, [
            '{{ listingRequest }}' => $listingRequestClass,
            '{{listingRequest}}' => $listingRequestClass,
            '{{ storeRequest }}' => $storeRequestClass,
            '{{storeRequest}}' => $storeRequestClass,
            '{{ updateRequest }}' => $updateRequestClass,
            '{{updateRequest}}' => $updateRequestClass,
            '{{ namespacedListingRequest }}' => $namespace.'\\'.$listingRequestClass,
            '{{namespacedListingRequest}}' => $namespace.'\\'.$listingRequestClass,
            '{{ namespacedStoreRequest }}' => $namespace.'\\'.$storeRequestClass,
            '{{namespacedStoreRequest}}' => $namespace.'\\'.$storeRequestClass,
            '{{ namespacedUpdateRequest }}' => $namespace.'\\'.$updateRequestClass,
            '{{namespacedUpdateRequest}}' => $namespace.'\\'.$updateRequestClass,
        ]);
    }

    /**
     * @param  array<string, string>  $replace
     * @param  string  $modelClass
     * @return array<string, string>
     */
    protected function buildResourceReplacements(array $replace, $modelClass): array
    {
        $namespace = $this->getNamespaceWith('App\\Http\\Resources');

        $classBasename = class_basename($modelClass);
        $resourceClass = $classBasename.'Resource';
        $collectionClass = $classBasename.'Collection';

        return array_merge($replace, [
            '{{ namespacedCollection }}' => $namespace.'\\'.$collectionClass,
            '{{namespacedCollection}}' => $namespace.'\\'.$collectionClass,
            '{{ namespacedResource }}' => $namespace.'\\'.$resourceClass,
            '{{namespacedResource}}' => $namespace.'\\'.$resourceClass,
            '{{ collection }}' => $collectionClass,
            '{{collection}}' => $collectionClass,
            '{{ resource }}' => $resourceClass,
            '{{resource}}' => $resourceClass,
        ]);
    }

    protected function getNamespaceWith(string $namespace): string
    {
        $rootNamespace = $this->rootNamespace();
        $defaultNamespace = $this->getDefaultNamespace(trim($rootNamespace, '\\'));
        $controllerNamespace = $this->getNamespace($this->getStringValue($this->argument('name')));
        if (! $controllerNamespace) {
            $controllerNamespace = $defaultNamespace;
        }

        return Str::replaceFirst($defaultNamespace, $namespace, $controllerNamespace);
    }
}
