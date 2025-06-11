<?php

namespace OnePkg\LaravelCrudGenerator;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CrudMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-pkg:make-crud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成CRUD文件';
    
    public function __construct()
    {
        parent::__construct();
        $this->specifyParameters();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $table = $this->argument('name');
        $name = Str::studly($table);

        $this->call('one-pkg:make-model', [
            'name' => $this->getModelName($name),
            '--table' => $table,
            '--force' => $this->option('force'),
        ]);
        $this->call('one-pkg:make-request', [
            'name' => $this->getIndexRequestName($name),
            '--table' => $table,
            '--force' => $this->option('force'),
        ]);
        $this->call('one-pkg:make-request', [
            'name' => $this->getStoreRequestName($name),
            '--table' => $table,
            '--force' => $this->option('force'),
        ]);
        $this->call('one-pkg:make-request', [
            'name' => $this->getUpdateRequestName($name),
            '--table' => $table,
            '--force' => $this->option('force'),
        ]);
        $this->call('one-pkg:make-resource', [
            'name' => $this->getResourceName($name),
            '--parent' => Config::get('crud-generator.parentJsonResource'),
            '--force' => $this->option('force'),
        ]);
        $this->call('one-pkg:make-resource', [
            'name' => $this->getCollectionName($name),
            '--parent' => Config::get('crud-generator.parentResourceCollection'),
            '--force' => $this->option('force'),
        ]);
        $this->call('one-pkg:make-controller', [
            'name' => $this->getControllerName($name),
            '--model' => $this->getModelName($name),
            '--perPageParam' => Config::get('crud-generator.perPageParam'),
            '--perPage' => Config::get('crud-generator.perPage'),
            '--force' => $this->option('force'),
        ]);

        $controllerName = $this->getControllerName($name);
        $this->addRoute($controllerName);

        return 0;
    }

    protected function getModelName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedModel'), $name);
    }

    protected function getIndexRequestName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedRequest'), "{$name}ListRequest");
    }

    protected function getStoreRequestName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedRequest'), "{$name}StoreRequest");
    }

    protected function getUpdateRequestName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedRequest'), "{$name}UpdateRequest");
    }

    protected function getResourceName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedResource'), "{$name}Resource");
    }

    protected function getCollectionName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedResource'), "{$name}Collection");
    }

    protected function getControllerName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedController'), "{$name}Controller");
    }

    protected function getNamespace(): string
    {
        $namespace = $this->option('namespace');
        if (!$namespace) {
            return '';
        }
        return trim($namespace, '\\') . '\\';
    }

    protected function buildName(string $namespace, string $name): string
    {
        $prefix = $this->getNamespace();

        return "{$namespace}\\{$prefix}{$name}";
    }

    protected function addRoute($controller)
    {
        $route = $this->option('route');
        if (!$route) {
            $route = 'api';
        }
        $routesPath = base_path("routes/{$route}.php");

        $uri = $this->buildUri($controller);
        $routeLine = "\nRoute::apiResource('{$uri}', '\\\\'.{$controller}::class);\n";

        file_put_contents($routesPath, $routeLine, FILE_APPEND);
    }

    protected function buildUri(string $controller)
    {
        return (string) Str::of($controller)
            ->replaceFirst('App\\Http\\Controllers\\', '')
            ->replaceLast('Controller', '')
            ->replace('\\', '/')
            ->lower();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the table.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the file already exists'],
            ['route', null, InputOption::VALUE_REQUIRED, 'File name of the route.'],
            ['namespace', null, InputOption::VALUE_REQUIRED, 'Namespace of the controller.'],
        ];
    }
}
