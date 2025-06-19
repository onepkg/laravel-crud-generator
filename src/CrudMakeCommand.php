<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CrudMakeCommand extends Command
{
    use CommandHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'onepkg:make-crud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成CRUD文件';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new controller creator command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = Str::ucfirst($this->getNameArgument());
        $table = $this->getTable($name);

        $this->call('onepkg:make-model', [
            'name' => $this->getModelName($name),
            '--table' => $table,
            '--force' => $this->option('force'),
        ]);
        $this->call('onepkg:make-request', [
            'name' => $this->getListingRequestName($name),
            '--table' => $table,
            '--force' => $this->option('force'),
        ]);
        $this->call('onepkg:make-request', [
            'name' => $this->getStoreRequestName($name),
            '--table' => $table,
            '--force' => $this->option('force'),
        ]);
        $this->call('onepkg:make-request', [
            'name' => $this->getUpdateRequestName($name),
            '--table' => $table,
            '--force' => $this->option('force'),
        ]);
        $this->call('onepkg:make-resource', [
            'name' => $this->getResourceName($name),
            '--force' => $this->option('force'),
        ]);
        $this->call('onepkg:make-resource', [
            'name' => $this->getCollectionName($name),
            '--force' => $this->option('force'),
        ]);
        $this->call('onepkg:make-controller', [
            'name' => $this->getControllerName($name),
            '--model' => $this->getModelName($name),
            '--force' => $this->option('force'),
        ]);

        $controllerName = $this->getControllerName($name);
        $this->addRoute($controllerName);

        return 0;
    }

    protected function getTable(string $name): string
    {
        $table = $this->option('table');

        return $table ? $this->getStringValue($table) : Str::snake(Str::plural($name));
    }

    protected function getModelName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedModel', ''), $name);
    }

    protected function getListingRequestName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedRequest', ''), "{$name}ListingRequest");
    }

    protected function getStoreRequestName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedRequest', ''), "{$name}StoreRequest");
    }

    protected function getUpdateRequestName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedRequest', ''), "{$name}UpdateRequest");
    }

    protected function getResourceName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedResource', ''), "{$name}Resource");
    }

    protected function getCollectionName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedResource', ''), "{$name}Collection");
    }

    protected function getControllerName(string $name): string
    {
        return $this->buildName(Config::get('crud-generator.namespacedController', ''), "{$name}Controller");
    }

    protected function buildName(string $namespace, string $name): string
    {
        if (! $namespace) {
            return $name;
        }

        return "{$namespace}\\{$name}";
    }

    protected function addRoute(string $controller): void
    {
        $route = $this->getStringValue($this->option('route'));
        if (! $route) {
            $route = 'api';
        }
        $routesPath = base_path("routes/{$route}.php");

        $uri = $this->buildUri($controller);
        $routeLine = $this->buildRoute($uri, $controller);

        file_put_contents($routesPath, $routeLine, FILE_APPEND);
    }

    protected function buildUri(string $controller): string
    {
        return (string) Str::of($controller)
            ->replaceFirst('App\\Http\\Controllers\\', '')
            ->replaceLast('Controller', '')
            ->replace('\\', '/')
            ->lower();
    }

    protected function buildRoute(string $uri, string $controller): string
    {
        $stub = $this->files->get($this->getStub());

        return str_replace(
            ['{{ uri }}', '{{ controller }}'],
            [$uri, $controller],
            $stub
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/route.crud.stub');
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
     * Get the console command arguments.
     *
     * @return array<int, array<int, string|int|null>>
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array<int, string|int|null>>
     */
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the file already exists'],
            ['route', null, InputOption::VALUE_REQUIRED, 'File name of the route.'],
            ['table', null, InputOption::VALUE_REQUIRED, 'The table name.'],
        ];
    }
}
