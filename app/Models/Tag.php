<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    use Sluggable;
    use BelongsToTenant;

    public $guarded = [
        'slug',
        'project_id',
    ];

    public function releases()
    {
        return $this->belongsToMany(Release::class)->withTimestamps();
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}
