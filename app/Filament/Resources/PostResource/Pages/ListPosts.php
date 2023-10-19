<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;
    protected ?string $subheading = 'Crie, edite e gerencie as postagems do seu site.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Criar postagem'),
        ];
    }
}
