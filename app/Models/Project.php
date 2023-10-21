<?php

namespace App\Models;

use Filament\Models\Contracts\{HasAvatar, HasName, HasCurrentTenantLabel};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Project extends Model implements HasName, HasCurrentTenantLabel, HasAvatar
{
    use HasFactory;
    use Sluggable;

    public $guarded = [
        'owner_id'
    ];

    public $casts = [
        'settings' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function releases()
    {
        return $this->hasMany(Release::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public static $defaultSettings = [
        'theme' => 'light',
        'public' => true,
        'indexing' => true,
        'adult' => false,
    ];

    public function updateSettings(array $data)
    {
        $data = collect($data)
            // ->dd() // Debug
            ->whereNotNull();
        $projectFields = ['title', 'slug'];

        $this->update([
            ...$data->only($projectFields),
            'settings' => [
                ...$this->settings ?? [],
                ...$data->except($projectFields),
            ],
        ]);
    }

    protected static function booted(): void
    {
        if (auth()->check()) {
            static::creating(function (Project $project) {
                $project->owner_id = auth()->user()->id;
            });
        }

        static::creating(function (Project $project) {
            $project->settings = self::$defaultSettings;
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function getFilamentName(): string
    {
        return $this->title;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return null;
        // return $this->avatar_url;
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Projeto:';
    }
}
