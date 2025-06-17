<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\ModelMakeCommand as ConsoleModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends ConsoleModelMakeCommand
{
    use CommandHelper;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'onepkg:make-model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        GeneratorCommand::handle();
    }

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
            ->replaceTableName($stub, $name)
            ->replaceFillableValue($stub, $name)
            ->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * @param  string  $stub
     * @return $this
     */
    protected function replaceTableName(&$stub, string $name)
    {
        $searches = [
            ['DummyTable'],
            ['{{ table }}'],
            ['{{table}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                $this->getTable($name),
                $stub
            );
        }

        return $this;
    }

    /**
     * Replace fillable value var for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceFillableValue(&$stub, string $name)
    {
        $fillable = $this->getFillable($name);

        $searches = [
            ['DummyFillable'],
            ['{{ fillable }}'],
            ['{{fillable}}'],
        ];

        $fillableStr = '';
        foreach ($fillable as $item) {
            $fillableStr .= "'{$item}', ";
        }
        $fillableStr = trim($fillableStr, ', ');

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                $fillableStr,
                $stub
            );
        }

        return $this;
    }

    /**
     * @return array<int, string>
     */
    protected function getFillable(string $name): array
    {
        $table = $this->getTable($name);

        $columns = Schema::getColumnListing($table);
        $guarded = $this->getDefaultGuarded();
        $fillable = array_diff($columns, $guarded);

        return $fillable;
    }

    protected function getTable(string $name): string
    {
        if ($this->hasOption('table') && $this->option('table')) {
            return $this->getStringValue($this->option('table'));
        }

        $table = class_basename($name);
        $table = Str::snake(Str::pluralStudly($table));

        return $table;
    }

    /**
     * @return array<int, string>
     */
    protected function getDefaultGuarded(): array
    {
        return ['id', 'created_at', 'updated_at'];
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/model.crud.stub');
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
     * @return array<int, array<int, string|int|null>>
     */
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists.'],
            ['table', null, InputOption::VALUE_REQUIRED, 'The table name.'],
        ];
    }
}
