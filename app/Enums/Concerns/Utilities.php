<?php

namespace App\Enums\Concerns;

trait Utilities
{
    public static function values(): array
    {
        return array_column(static::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }
}
