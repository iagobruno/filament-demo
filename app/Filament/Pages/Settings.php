<?php

namespace App\Filament\Pages;

use App\Models\Blog;
use Filament\Forms\Form;
use Filament\Forms;
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
                Forms\Components\Section::make('Geral')
                    ->description('Configurações básicas')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Este é o título que será mostrado na parte superior do blog.'),
                        Forms\Components\TextInput::make('description')
                            ->label('Descrição')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('adult')
                            ->label('Conteúdo adulto')
                            ->helperText('Mostrar aviso aos leitores do blog')
                            ->default(false)
                    ]),

                Forms\Components\Section::make('Domínio')
                    ->description('As pessoas encontrarão seu blog on-line neste endereço da Web')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('Endereço do blog')
                            ->required()
                            ->alphaDash()
                            ->maxLength(24)
                            ->live(debounce: 300)
                            ->suffix(fn () => '.' . preg_replace('/^http(s)?:\/\//', '', config('app.url')))
                            ->unique('blogs', 'slug', ignorable: Filament::getTenant()),
                    ]),

                Forms\Components\Section::make('Aparência')
                    ->description('Personalize como as pessoas verão seu blog')
                    ->aside()
                    ->schema([
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\FileUpload::make('logo')
                                ->label('Logotipo')
                                ->image()
                                ->panelAspectRatio('2:1'),
                            Forms\Components\FileUpload::make('favicon')
                                ->label('Favicon')
                                ->image()
                                ->imageCropAspectRatio('1:1')
                                ->panelAspectRatio('2:1')
                                ->helperText('Ícone que será mostrado na aba do navegador do usuário'),
                        ]),
                        Forms\Components\Select::make('theme')
                            ->label('Esquema de cores')
                            ->options([
                                'dark' => 'Escuro',
                                'light' => 'Claro'
                            ])
                            ->selectablePlaceholder(false),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Cor primária')
                            ->helperText('Será usado nos links e botões'),
                        Forms\Components\ColorPicker::make('background_color')
                            ->label('Plano de fundo'),
                    ]),

                Forms\Components\Section::make('Privacidade')
                    ->description('Configurações de segurança')
                    ->aside()
                    ->schema([
                        Forms\Components\Toggle::make('public')
                            ->label('Blog público')
                            ->helperText('Pemitir que qualquer pessoa com o endereço acesse seu blog. Ao desativar essa opção só você poderá visualizar as páginas.'),
                        Forms\Components\Toggle::make('indexing')
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
