<?php

namespace OnePkg\LaravelCrudGenerator;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Console\ControllerMakeCommand as ConsoleControllerMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ControllerMakeCommand extends ConsoleControllerMakeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-pkg:make-controller';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
        $this->specifyParameters();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/controller.model.api.stub');
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
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));

        $requestReplace = $this->buildFormRequestReplacements($replace, $modelClass);
        $resourceReplace = $this->buildResourceReplacements($replace, $modelClass);
        $paginatorReplace = $this->buildPaginatorReplacements($replace);

        return array_merge($requestReplace, $resourceReplace, $paginatorReplace, [
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
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @param  string  $modelClass
     * @return array
     */
    protected function buildFormRequestReplacements(array $replace, $modelClass)
    {
        $namespace = $this->getNamespaceWith('App\\Http\\Requests');

        $classBasename = class_basename($modelClass);
        $indexRequestClass = 'Index'.$classBasename.'Request';
        $storeRequestClass = 'Store'.$classBasename.'Request';
        $updateRequestClass = 'Update'.$classBasename.'Request';

        return array_merge($replace, [
            '{{ indexRequest }}' => $indexRequestClass,
            '{{indexRequest}}' => $indexRequestClass,
            '{{ storeRequest }}' => $storeRequestClass,
            '{{storeRequest}}' => $storeRequestClass,
            '{{ updateRequest }}' => $updateRequestClass,
            '{{updateRequest}}' => $updateRequestClass,
            '{{ namespacedIndexRequest }}' => $namespace.'\\'.$indexRequestClass,
            '{{namespacedIndexRequest}}' => $namespace.'\\'.$indexRequestClass,
            '{{ namespacedStoreRequest }}' => $namespace.'\\'.$storeRequestClass,
            '{{namespacedStoreRequest}}' => $namespace.'\\'.$storeRequestClass,
            '{{ namespacedUpdateRequest }}' => $namespace.'\\'.$updateRequestClass,
            '{{namespacedUpdateRequest}}' => $namespace.'\\'.$updateRequestClass,
        ]);
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @param  string  $modelClass
     * @return array
     */
    protected function buildResourceReplacements(array $replace, $modelClass)
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

    protected function getNamespaceWith(string $namespace)
    {
        $rootNamespace = $this->rootNamespace();
        $defaultNamespace = $this->getDefaultNamespace(trim($rootNamespace, '\\'));
        $controllerNamespace = $this->getNamespace($this->argument('name'));

        return Str::replaceFirst($defaultNamespace, $namespace, $controllerNamespace);
    }

    protected function buildPaginatorReplacements(array $replace)
    {
        return array_merge($replace, [
            '{{ perPage }}' => $this->option('perPageParam'),
            '{{perPage}}' => $this->option('perPageParam'),
            '{{ defaultPageSize }}' => $this->option('perPage'),
            '{{defaultPageSize}}' => $this->option('perPage'),
        ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        return array_merge($options, [
            ['perPageParam', null, InputOption::VALUE_OPTIONAL, 'Pagination parameter', 'per-page'],
            ['perPage', null, InputOption::VALUE_OPTIONAL, 'Pagination parameter', 10],
        ]);
    }
}
