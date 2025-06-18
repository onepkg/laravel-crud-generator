<?php

namespace Onepkg\LaravelCrudGenerator;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RequestJsonBodyMakeCommand extends Command
{
    use CommandHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onepkg:make-request-json-body {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a request body in JSON format';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $table = $this->getStringValue($this->argument('table'));

        $columns = Schema::getColumns($table);
        $count = count($columns);
        $i = 0;
        $json = "{\n";
        foreach ($columns as $column) {
            $i++;
            $columnName = $column['COLUMN_NAME'];
            if (in_array($columnName, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $value = $column['COLUMN_DEFAULT'];

            $columnType = $column['COLUMN_TYPE'];
            if ($columnType === 'timestamp' || $columnType === 'datetime') {
                $now = Carbon::now();
                if (Str::contains($columnName, 'start')) {
                    $value = $now->startOfDay()->toDateTimeString();
                } elseif (Str::contains($columnName, 'end')) {
                    $value = $now->endOfDay()->toDateTimeString();
                } else {
                    $value = $now->toDateTimeString();
                }
            } elseif ($columnType === 'date') {
                $now = Carbon::now();
                if (Str::contains($columnName, 'start')) {
                    $value = $now->startOfDay()->toDateString();
                } elseif (Str::contains($columnName, 'end')) {
                    $value = $now->endOfDay()->toDateString();
                } else {
                    $value = $now->toDateString();
                }
            }

            if ($value === null) {
                $value = 'null';
            }

            if (! Str::contains($columnType, 'int') && $value !== 'null') {
                $value = str_replace('"', '\\"', $value);
                $value = "\"$value\"";
            }

            $json .= sprintf(
                "    \"%s\": %s%s // %s\n",
                $columnName,
                $value,
                $i < $count ? ',' : '',
                $column['COLUMN_COMMENT']
            );
        }
        $json = rtrim($json, ",\n")."\n}\n";
        $this->info($json);

        return 0;
    }
}
