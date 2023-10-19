<?php

namespace App\Filament\Pages\Tenancy;

use App\Filament\Resources\PostResource;
use App\Models\Blog;
use Filament\Facades\Filament;
use Filament\Forms\Components\{Placeholder, TextInput};
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;

class RegisterBlog extends RegisterTenant
{
    public static ?string $slug = 'criar-blog';

    public static function getLabel(): string
    {
        return 'Criar novo blog';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 300),
                Placeholder::make('slug')
                    ->label('O endereço do seu blog será:')
                    ->content(function (Get $get): string {
                        $slug = SlugService::createSlug(Blog::class, 'slug', $get('title'));
                        return route('blog-homepage', ['blog' => $slug]);
                    })
                    ->visible(fn (Get $get) => strlen($get('title') > 0)),
            ]);
    }

    protected function handleRegistration(array $data): Blog
    {
        $blog = Blog::create($data);

        Notification::make()
            ->title('Blog criado com sucesso!')
            ->success()
            ->seconds(60)
            ->actions([
                NotificationAction::make('create-post')
                    ->label('Criar primeira postagem')
                    ->url(PostResource::getUrl('create', ['tenant' => $blog->slug]))
                    ->button(),
                NotificationAction::make('view')
                    ->label('Visualizar')
                    ->url(route('blog-homepage', [$blog->slug]))
                    ->openUrlInNewTab(),
            ])
            ->send();

        return $blog;
    }

    protected function getRedirectUrl(): ?string
    {
        return Filament::getUrl($this->tenant);
    }
}
