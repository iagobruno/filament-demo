<?php

namespace App\Filament\Resources;

use App\Enums\ReleaseType;
use App\Enums\ReleaseStatus;
use App\Filament\Resources\ReleaseResource\Pages;
use App\Filament\Resources\ReleaseResource\RelationManagers;
use App\Models\Release;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Filament\Facades\Filament;
use Filament\Forms\Components\{Grid, Placeholder, RichEditor, Section, Select, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\{TextColumn};
use Filament\Tables\Filters\{SelectFilter};
use Filament\Tables\Actions\{Action, BulkActionGroup, EditAction, DeleteAction, DeleteBulkAction};
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReleaseResource extends Resource
{
    protected static ?string $model = Release::class;
    protected static ?string $modelLabel = 'lançamento';
    protected static ?string $pluralModelLabel = 'lançamentos';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Content card
                Section::make()->schema([
                    Grid::make()->schema([
                        Select::make('type')
                            ->label('Tipo:')
                            ->required()
                            ->options(fn () => ReleaseType::generateSelectOptions())
                            ->default(ReleaseType::DEFAULT)
                            ->selectablePlaceholder(false)
                            ->columnSpan(1),
                        TextInput::make('title')
                            ->label('Título:')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->helperText(function ($state, $record) {
                                if ($record?->slug) $slug = $record->slug;
                                else $slug = $state ? SlugService::createSlug(Release::class, 'slug', $state) : '...';

                                return route('blog-release', ['release' => $slug, 'project' => Filament::getTenant()->slug]);
                            })
                            ->autocomplete(false)
                            ->columnSpan(3),
                    ])
                        ->columns(4),
                    RichEditor::make('content')
                        ->label('')
                        ->required()
                        ->disableToolbarButtons(['attachFiles'])
                        ->columnSpanFull(),
                ])
                    ->columns([
                        'sm' => 2,
                    ])
                    ->columnSpan(2),

                // Info card
                Section::make()->schema([
                    Select::make('status')
                        ->label('Status:')
                        ->required()
                        ->options(fn () => ReleaseStatus::generateSelectOptions())
                        ->default(ReleaseStatus::DEFAULT)
                        ->selectablePlaceholder(false),
                    Select::make('tags')
                        ->label('Tags:')
                        ->relationship(
                            'tags',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->whereBelongsTo(Filament::getTenant())
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                        ]),
                    Placeholder::make('created_at')
                        ->label('Criado em:')
                        ->content(fn (?Release $record) => $record?->created_at->format('d/m/Y à\s H:i') ?? '-'),
                    Placeholder::make('updated_at')
                        ->label('Atualizado em:')
                        ->content(fn (?Release $record) => $record?->updated_at->format('d/m/Y à\s H:i') ?? '-'),
                    Placeholder::make('words_count')
                        ->label('Palavras:')
                        ->content(fn (?Release $record) => str($record?->content)->wordCount())
                        ->extraAttributes([
                            'x-init' => "
                                    document.getElementById('data.content').addEventListener('keyup', debounce((evt) => {
                                        \$el.innerText = wordCount(evt.target.innerText);
                                    }))
                                    function wordCount(text) {
                                        return text.trim().split(/\w+/gim).length-1;
                                    }
                                    function debounce(func, timeout = 500){
                                        let timer;
                                        return (...args) => {
                                          clearTimeout(timer);
                                          timer = setTimeout(() => func.apply(this, args), timeout);
                                        };
                                    }
                                "
                        ]),
                ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->size('sm')
                    ->toggleable(),
                TextColumn::make('title')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->size('sm'),
                TextColumn::make('tags.name')
                    ->label('Tags')
                    ->badge()
                    ->color('gray')
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y à\s H:i')
                    ->size('sm')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Modificado')
                    ->dateTime('d/m/Y à\s H:i')
                    ->size('sm')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'DESC')
            ->filters([
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status:')
                    ->options([
                        'draft' => 'Rascunhos',
                        'public' => 'Públicos',
                        'private' => 'Privados',
                    ])
                    ->placeholder('Todos')
                    ->query(function (Builder $query, $state) {
                        return match ($state['value']) {
                            'draft' => $query->where('status', ReleaseStatus::Draft->value),
                            'public' => $query->where('status', ReleaseStatus::Public->value),
                            'private' => $query->where('status', ReleaseStatus::Private->value),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                EditAction::make(),
                Action::make('view-live')
                    ->label('Visualizar')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(fn (Release $record): string => route('blog-release', ['release' => $record->slug, 'project' => Filament::getTenant()->slug]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum lançamento ainda')
            ->emptyStateDescription('')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Criar primeiro lançamento')
                    ->url(ReleaseResource::getUrl('create'))
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
            'index' => Pages\ListReleases::route('/'),
            'create' => Pages\CreateRelease::route('/create'),
            'edit' => Pages\EditRelease::route('/{record}/edit'),
        ];
    }
}
