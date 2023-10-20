<?php

namespace App\Filament\Pages\Tenancy;

use App\Filament\Resources\ReleaseResource;
use App\Models\Project;
use Filament\Facades\Filament;
use Filament\Forms\Components\{Placeholder, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;

class RegisterProject extends RegisterTenant
{
    public static ?string $slug = 'criar-projeto';

    public static function getLabel(): string
    {
        return 'Criar novo projeto';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 300)
                    ->autocomplete(false),
                Placeholder::make('slug')
                    ->label('O endereço será:')
                    ->content(function (Get $get): string {
                        $slug = SlugService::createSlug(Project::class, 'slug', $get('title'));
                        return route('blog-homepage', [$slug]);
                    })
                    ->visible(fn (Get $get) => strlen($get('title') > 0)),
                Toggle::make('terms_of_service')
                    ->label('Concordo com os termos de serviço.')
                    ->accepted(),
            ]);
    }

    protected function handleRegistration(array $data): Project
    {
        $data = collect($data)
            ->except(['terms_of_service'])
            ->toArray();
        $project = Project::create($data);

        Notification::make()
            ->title('Projeto criado com sucesso!')
            ->success()
            ->seconds(60)
            ->actions([
                NotificationAction::make('create-release')
                    ->label('Criar primeiro lançamento')
                    ->url(ReleaseResource::getUrl('create', ['tenant' => $project->slug]))
                    ->button(),
                NotificationAction::make('view')
                    ->label('Visualizar')
                    ->url(route('blog-homepage', [$project->slug]))
                    ->openUrlInNewTab(),
            ])
            ->send();

        return $project;
    }

    protected function getRedirectUrl(): ?string
    {
        return Filament::getUrl($this->tenant);
    }
}
