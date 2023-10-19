<?php

namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasIcon, HasLabel};

enum PostStatus: string implements HasLabel, HasIcon, HasColor
{
    case Draft = 'draft';
    case Public = 'public';
    case Private = 'private';

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Public => 'Público',
            self::Private => 'Privado',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Public => 'success',
            self::Private => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-m-pencil',
            self::Public => 'heroicon-m-globe-alt',
            self::Private => 'heroicon-m-lock-closed',
        };
    }
}
