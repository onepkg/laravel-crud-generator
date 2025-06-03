<?php

namespace OnePkg\LaravelCrudGenerator;

use Illuminate\Database\Eloquent\Builder;

trait AutoQueryBuilder
{
    public function scopeSearch(Builder $query, array $validated, array $customized = [])
    {
        auto_build_query($query, $validated, $customized);
    }
}
