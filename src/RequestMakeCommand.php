<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Foundation\Console\RequestMakeCommand as ConsoleRequestMakeCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RequestMakeCommand extends ConsoleRequestMakeCommand
{
    use CommandHelper;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'onepkg:make-request';

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
            ->replaceValidationRules($stub, $name)
            ->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the rules for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceValidationRules(&$stub, $name)
    {
        $rules = $this->getValidationRules($name);

        $searches = [
            ['DummyRules'],
            ['{{ rules }}'],
            ['{{rules}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                $rules,
                $stub
            );
        }

        return $this;
    }

    protected function getValidationRules(string $name): string
    {
        $table = $this->getTable($name);
        $columns = Schema::getColumns($table);
        $indexes = Schema::getUniqueIndexes($table);

        $rules = '';
        foreach ($columns as $column) {
            $columnName = $column['COLUMN_NAME'];

            if (in_array($columnName, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $columnValidationRules = $this->getColumnValidationRules($column, Arr::get($indexes, $columnName, []));
            if (! Str::startsWith($columnValidationRules, '[') || ! Str::endsWith($columnValidationRules, ']')) {
                $columnValidationRules = "'{$columnValidationRules}'";
            }
            $rules .= sprintf(
                "\t\t\t'%s' => %s,\n",
                $columnName,
                $columnValidationRules
            );
        }

        return rtrim($rules, "\n");
    }

    /**
     * @param  array<string, mixed>  $column
     * @param  array<int, array<string, mixed>>  $index
     */
    protected function getColumnValidationRules(array $column, array $index = []): string
    {
        $name = $this->getStringValue($this->argument('name'));
        $rules = [];

        if ($column['IS_NULLABLE'] === 'NO' && $column['COLUMN_DEFAULT'] === null && ! $this->isListingRequest($name)) {
            $rules[] = 'required';
        }

        $max = $column['CHARACTER_MAXIMUM_LENGTH'];

        $dataType = $column['DATA_TYPE'];
        if (Str::contains($dataType, 'int')) {
            $rules[] = 'integer';
        } elseif (Str::contains($dataType, 'char') || Str::contains($dataType, 'text')) {
            $rules[] = 'string';
        } elseif (in_array($dataType, ['datetime', 'timestamp'])) {
            $rules[] = 'date_format:Y-m-d H:i:s';
        } elseif (in_array($dataType, ['date'])) {
            $rules[] = 'date';
        } elseif (in_array($dataType, ['decimal', 'float', 'double'])) {
            $rules[] = 'numeric';
        } elseif ($dataType === 'json') {
            $rules[] = 'json';
        } elseif ($dataType === 'enum') {
            $enum = preg_match('/enum\((\S+)\)/', $column['COLUMN_TYPE'], $matches) ? $matches[1] : '';
            if ($enum) {
                $enum = str_replace("'", '', $enum);
                $rules[] = "in:{$enum}";
            }
            $max = null;
        }

        if ($max) {
            $rules[] = "max:{$max}";
        }

        $columnName = $column['COLUMN_NAME'];
        if (str_contains($columnName, 'email')) {
            $rules[] = 'email';
        } elseif (str_contains($columnName, 'url')) {
            $rules[] = 'url';
        }

        if ($this->isListingRequest($name)) {
            return implode('|', $rules);
        }

        $indexColumnCount = count($index);
        $table = $this->getTable($name);

        if ($indexColumnCount === 1) {
            $rules[] = sprintf('unique:%s,%s', $table, $columnName);
        }

        if ($indexColumnCount < 2) {
            return implode('|', $rules);
        }

        $format = 'Rule::unique(\'%s\')->where(function ($query) { %s })%s';
        $where = '';
        $ignore = '';
        foreach ($index as $indexColumn) {
            if ($column['COLUMN_NAME'] === $indexColumn['Column_name']) {
                continue;
            }

            $where .= sprintf('$query->where(\'%s\', $this->%s);', $indexColumn['Column_name'], $indexColumn['Column_name']);
        }
        $requestClass = class_basename($name);
        if (Str::startsWith($requestClass, 'Update')) {
            $ignore = '->ignore($this->id)';
        }

        $rules[] = sprintf($format, $table, $where, $ignore);

        $result = '[';
        foreach ($rules as $rule) {
            if (Str::startsWith($rule, 'Rule::unique')) {
                $result .= "{$rule}, ";
            } else {
                $result .= "'{$rule}', ";
            }
        }
        $result = trim($result, ', ');
        $result .= ']';

        return $result;
    }

    protected function isListingRequest(string $name): bool
    {
        return Str::endsWith($name, 'ListingRequest');
    }

    protected function getTable(string $name): string
    {
        $table = $this->getStringValue($this->option('table'));
        if ($table) {
            return $table;
        }

        $model = $this->getStringValue($this->option('model'));
        if ($model) {
            $table = Str::snake(Str::pluralStudly(class_basename($model)));

            return $table;
        }

        $table = class_basename($name);
        $table = Str::replaceLast('Request', '', $table);
        $table = Str::snake(Str::pluralStudly($table));

        return $table;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/request.crud.stub');
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
     * @return array<int, array<int|string|null>>
     */
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the request already exists'],
            ['table', null, InputOption::VALUE_REQUIRED, 'The table name.'],
            ['model', null, InputOption::VALUE_REQUIRED, 'The model class.'],
        ];
    }
}
