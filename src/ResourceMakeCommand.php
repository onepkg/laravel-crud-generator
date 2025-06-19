<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Foundation\Console\ResourceMakeCommand as ConsoleResourceMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class ResourceMakeCommand extends ConsoleResourceMakeCommand
{
    use CommandHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'onepkg:make-resource';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this
            ->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->collection()
            ? $this->resolveStubPath('/stubs/resource-collection.crud.stub')
            : $this->resolveStubPath('/stubs/resource.crud.stub');
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
     * Get the console command options.
     *
     * @return array<int, array<string|null|int>>
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the resource already exists'],
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'],
        ];
    }
}
