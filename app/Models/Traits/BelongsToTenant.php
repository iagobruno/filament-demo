<?php

namespace App\Models\Traits;

use App\Models\Project;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected static function bootBelongsToTenant(): void
    {
        if ($tenant = Filament::getTenant()) {
            static::creating(function (Model $model) use ($tenant) {
                $model->forceFill(['project_id' => $tenant->id]);
            });

            // static::addGlobalScope('tenant', function (Builder $query) use ($tenant) {
            //     $query->whereBelongsTo($tenant->id);
            // });
        }
    }
}
