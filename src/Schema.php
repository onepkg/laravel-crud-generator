<?php

namespace Onepkg\LaravelCrudGenerator;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Schema
{
    /**
     * @return array<string>
     */
    public static function getColumnListing(string $table): array
    {
        $columns = self::getColumns($table);

        return array_column($columns, 'COLUMN_NAME');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getColumns(string $table): array
    {
        $table = self::getTrueTable($table);
        $sql = "select * from information_schema.columns where table_name = '{$table}'";
        $columns = collect(DB::select($sql))
            ->map(function ($item) {
                return (array) $item;
            })
            ->sortBy('ORDINAL_POSITION')
            ->all();

        return $columns;
    }

    protected static function getTrueTable(string $table): string
    {
        /**
         * @var Connection
         */
        $connection = DB::connection();
        $prefix = $connection->getTablePrefix();
        if (! Str::startsWith($table, $prefix)) {
            $table = $prefix.$table;
        }

        return $table;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function getUniqueIndexes(string $table): array
    {
        $table = self::getTrueTable($table);

        $sql = "SHOW INDEX FROM {$table} where non_unique=0 and key_name!='PRIMARY'";
        $indexes = DB::select($sql);
        $grouped = collect($indexes)
            ->map(function ($item) {
                return (array) $item;
            })
            ->groupBy('Key_name')
            ->all();
        $result = [];
        foreach ($grouped as $index => $items) {
            $replace = $index;
            foreach ($items as $item) {
                $replace = $item['Column_name'];
            }
            $result[$replace] = $items->all();
        }

        return $result;
    }
}
