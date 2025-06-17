<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Onepkg\LaravelCrudGenerator\Schema;

if (! function_exists('auto_build_query')) {
    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, callable>  $customized
     */
    function auto_build_query(Builder $query, array $validated, array $customized = []): void
    {
        /**
         * @var \Illuminate\Database\Eloquent\Model
         */
        $model = $query->getModel();
        $columns = collect(Schema::getColumns($model->getTable()))
            ->keyBy('COLUMN_NAME')
            ->all();

        foreach ($validated as $column => $value) {
            if (isset($customized[$column])) {
                $query->when($value !== '', $customized[$column]);

                continue;
            }

            $typeName = Arr::get($columns, "{$column}.COLUMN_TYPE");
            if ($typeName === null || $value === '') {
                continue;
            }

            if (strpos(',', $value) !== false) {
                $value = explode(',', $value);
            }

            if (Str::contains($typeName, 'int')) {
                if (is_array($value)) {
                    $values = collect($value)
                        ->map(function ($item) {
                            return (int) $item;
                        })
                        ->unique()
                        ->toArray();
                    $query->whereIn($column, $values);
                } else {
                    $query->where($column, (int) $value);
                }
            } elseif (Str::contains($typeName, 'datetime') || Str::contains($typeName, 'timestamp')) {
                if (! is_array($value) || ! isset($value[0])) {
                    continue;
                }
                $startTime = Carbon::make($value[0]);
                if (! $startTime) {
                    return;
                }
                if (isset($value[1])) {
                    $endTime = Carbon::make($value[1]);
                    if (! $endTime) {
                        return;
                    }
                } else {
                    $endTime = Carbon::now();
                }
                $query->whereBetween($column, [$startTime->startOfDay()->toDateTimeString(), $endTime->endOfDay()->toDateTimeString()]);
            } elseif (Str::contains($typeName, 'date')) {
                $startTime = Carbon::make($value[0]);
                if (! $startTime) {
                    return;
                }
                if (isset($value[1])) {
                    $endTime = Carbon::make($value[1]);
                    if (! $endTime) {
                        return;
                    }
                } else {
                    $endTime = Carbon::now();
                }
                $query->whereBetween($column, [$startTime->startOfDay()->toDateString(), $endTime->endOfDay()->toDateString()]);
            } else {
                if (is_array($value)) {
                    $values = collect($value)
                        ->map(function ($item) {
                            return (string) $item;
                        })
                        ->unique()
                        ->toArray();
                    $query->whereIn($column, $values);
                } else {
                    $query->where($column, (string) $value);
                }
            }
        }
    }
}
