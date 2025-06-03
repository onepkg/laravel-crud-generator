<?php

namespace OnePkg\LaravelCrudGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Schema
{
    public static function getColumnListing(string $table): array
    {
        $columns = self::getColumns($table);
        return array_column($columns, 'COLUMN_NAME');
    }

    public static function getColumns(string $table): array
    {
        $table = self::getTrueTable($table);
        $sql = "select * from information_schema.columns where table_name = '{$table}'";
        $columns = DB::select($sql);

        return $columns;
    }

    protected static function getTrueTable(string $table): string
    {
        $prefix = DB::getTablePrefix();
        if (!Str::startsWith($table, $prefix)) {
            $table = $prefix . $table;
        }

        return $table;
    }

    public static function getUniqueIndexes(string $table): array
    {
        $table = self::getTrueTable($table);

        $sql = "SHOW INDEX FROM {$table} where non_unique=0 and key_name!='PRIMARY'";
        $indexes = DB::select($sql);
        $grouped = collect($indexes)->groupBy('Key_name')->toArray();
        $result = [];
        foreach ($grouped as $index => $items) {
            $replace = $index;
            foreach  ($items as $item) {
                $replace = $item->Column_name;
            }
            $result[$replace] = $items;
        }

        return $result;
    }
}