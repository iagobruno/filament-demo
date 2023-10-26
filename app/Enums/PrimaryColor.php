<?php

namespace App\Enums;

use App\Enums\Concerns\Utilities;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;

enum PrimaryColor: string implements HasColor
{
    use Utilities;

    case Slate = 'slate';
    case Gray = 'gray';
    case Zinc = 'zinc';
    case Neutral = 'neutral';
    case Stone = 'stone';
    case Red = 'red';
    case Orange = 'orange';
    case Amber = 'amber';
    case Yellow = 'yellow';
    case Lime = 'lime';
    case Green = 'green';
    case Emerald = 'emerald';
    case Teal = 'teal';
    case Cyan = 'cyan';
    case Sky = 'sky';
    case Blue = 'blue';
    case Indigo = 'indigo';
    case Violet = 'violet';
    case Purple = 'purple';
    case Fuchsia = 'fuchsia';
    case Pink = 'pink';
    case Rose = 'rose';

    public const DEFAULT = self::Indigo;

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Slate => Color::Slate,
            self::Gray => Color::Gray,
            self::Zinc => Color::Zinc,
            self::Neutral => Color::Neutral,
            self::Stone => Color::Stone,
            self::Red => Color::Red,
            self::Orange => Color::Orange,
            self::Amber => Color::Amber,
            self::Yellow => Color::Yellow,
            self::Lime => Color::Lime,
            self::Green => Color::Green,
            self::Emerald => Color::Emerald,
            self::Teal => Color::Teal,
            self::Cyan => Color::Cyan,
            self::Sky => Color::Sky,
            self::Blue => Color::Blue,
            self::Indigo => Color::Indigo,
            self::Violet => Color::Violet,
            self::Purple => Color::Purple,
            self::Fuchsia => Color::Fuchsia,
            self::Pink => Color::Pink,
            self::Rose => Color::Rose,
        };
    }
}
