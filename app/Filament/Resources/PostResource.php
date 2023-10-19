<?php

namespace App\Filament\Resources;

use App\Enums\PostStatus;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $modelLabel = 'postagem';
    protected static ?string $pluralModelLabel = 'postagens';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título:')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->helperText(function ($state, $record) {
                        if ($record?->slug) $slug = $record->slug;
                        else $slug = $state ? SlugService::createSlug(Post::class, 'slug', $state) : '...';

                        return route('blog-post', ['post' => $slug, 'blog' => Filament::getTenant()->slug]);
                    })
                    ->autocomplete(false),
                Forms\Components\Select::make('status')
                    ->label('Status:')
                    ->required()
                    ->options(function () {
                        $options = [];
                        foreach (PostStatus::cases() as $item) {
                            $options[$item->value] = PostStatus::from($item->value)->getLabel();
                        }
                        return $options;
                    })
                    ->default('draft')
                    ->selectablePlaceholder(false),
                Forms\Components\RichEditor::make('content')
                    ->label('')
                    ->required()
                    ->disableToolbarButtons(['attachFiles'])
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y à\s H:i')
                    ->size('sm')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modificado')
                    ->dateTime('d/m/Y à\s H:i')
                    ->size('sm')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'DESC')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status:')
                    ->options([
                        'draft' => 'Rascunhos',
                        'public' => 'Públicos',
                        'private' => 'Privados',
                    ])
                    ->placeholder('Todos')
                    ->query(function (Builder $query, $state) {
                        return match ($state['value']) {
                            'draft' => $query->where('status', PostStatus::Draft->value),
                            'public' => $query->where('status', PostStatus::Public->value),
                            'private' => $query->where('status', PostStatus::Private->value),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view-live')
                    ->label('Visualizar')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(fn (Post $record): string => route('blog-post', ['post' => $record->slug, 'blog' => Filament::getTenant()->slug]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhuma postagem ainda')
            ->emptyStateDescription('')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Criar primeira postagem')
                    ->url(PostResource::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
