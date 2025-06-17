<?php

namespace Onepkg\LaravelCrudGenerator;

trait CommandHelper
{
    protected function getNameArgument(): string
    {
        $name = $this->argument('name');

        return $this->getStringValue($name);
    }

    /**
     * @param  mixed  $value
     */
    protected function getStringValue($value): string
    {
        if (! is_string($value)) {
            $value = '';
        }

        return $value;
    }
}
