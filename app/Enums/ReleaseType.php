<?php

namespace App\Enums;

use App\Enums\Concerns\Utilities;
use Filament\Support\Contracts\{HasColor, HasIcon, HasLabel};

enum ReleaseType: string implements HasLabel, HasIcon, HasColor
{
    use Utilities;

    case Feature = 'feature';
    case Update = 'update';
    case Bugfix = 'bugfix';

    public const DEFAULT = self::Feature;

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Feature => 'success',
            self::Update => 'info',
            self::Bugfix => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Feature => 'heroicon-m-fire',
            self::Update => 'heroicon-m-star',
            self::Bugfix => 'heroicon-m-bug-ant',
        };
    }
}
