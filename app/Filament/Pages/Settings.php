<?php

namespace App\Filament\Pages;

use App\Models\Blog;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, Grid, TextInput, Toggle, Select, ColorPicker, FileUpload};
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\{InteractsWithForms};
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;

class Settings extends Page
{
    use InteractsWithForms, InteractsWithFormActions;

    protected static ?string $title = 'Configurações';
    protected ?string $subheading = 'Gerencie as configurações do seu blog';
    protected static ?string $navigationLabel = 'Configurações';
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.settings';

    public array $data = [];

    public function mount(): void
    {
        $blog = Filament::getTenant();
        // dump($blog->toArray());
        $this->form->fill([
            ...$blog->settings,
            ...$blog->only(['title', 'slug']),
        ]);
    }

    public function save(): void
    {
        $data = collect($this->form->getState())
            // ->dd() // Debug
            ->whereNotNull();
        $blogFields = ['title', 'slug'];
        $blogSettings = $data->only($blogFields)->toArray();
        $updatedSettings = $data->except($blogFields)->toArray();

        $blog = Filament::getTenant();
        $blog->update([
            ...$blogSettings,
            'settings' => [
                ...$blog->settings ?? [],
                ...$updatedSettings,
            ],
        ]);

        Notification::make()
            ->title('Configurações atualizadas!')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Geral')
                    ->description('Configurações básicas')
                    ->aside()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Este é o título que será mostrado na parte superior do blog.'),
                        TextInput::make('description')
                            ->label('Descrição')
                            ->maxLength(255),
                        Toggle::make('adult')
                            ->label('Conteúdo adulto')
                            ->helperText('Mostrar aviso aos leitores do blog')
                            ->default(false)
                    ]),

                Section::make('Domínio')
                    ->description('As pessoas encontrarão seu blog on-line neste endereço da Web')
                    ->aside()
                    ->schema([
                        TextInput::make('slug')
                            ->label('Endereço do blog')
                            ->required()
                            ->alphaDash()
                            ->maxLength(24)
                            ->live(debounce: 300)
                            ->suffix(fn () => '.' . preg_replace('/^http(s)?:\/\//', '', config('app.url')))
                            ->unique('blogs', 'slug', ignorable: Filament::getTenant()),
                    ]),

                Section::make('Aparência')
                    ->description('Personalize como as pessoas verão seu blog')
                    ->aside()
                    ->schema([
                        Grid::make()->schema([
                            FileUpload::make('logo')
                                ->label('Logotipo')
                                ->image()
                                ->panelAspectRatio('2:1'),
                            FileUpload::make('favicon')
                                ->label('Favicon')
                                ->image()
                                ->imageCropAspectRatio('1:1')
                                ->panelAspectRatio('2:1')
                                ->helperText('Ícone que será mostrado na aba do navegador do usuário'),
                        ]),
                        Select::make('theme')
                            ->label('Esquema de cores')
                            ->options([
                                'dark' => 'Escuro',
                                'light' => 'Claro'
                            ])
                            ->selectablePlaceholder(false),
                        ColorPicker::make('color')
                            ->label('Cor primária')
                            ->helperText('Será usado nos links e botões'),
                        ColorPicker::make('background_color')
                            ->label('Plano de fundo'),
                    ]),

                Section::make('Privacidade')
                    ->description('Configurações de segurança')
                    ->aside()
                    ->schema([
                        Toggle::make('public')
                            ->label('Blog público')
                            ->helperText('Pemitir que qualquer pessoa com o endereço acesse seu blog. Ao desativar essa opção só você poderá visualizar as páginas.'),
                        Toggle::make('indexing')
                            ->label('Visível para mecanismos de pesquisa')
                            ->helperText('Permitir que mecanismos de pesquisa encontrem seu blog'),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Salvar alterações')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
