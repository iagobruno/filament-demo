<?php

namespace App\Filament\Pages;

use App\Models\Project;
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
    protected ?string $subheading = 'Gerencie as configurações do seu projeto';
    protected static ?string $navigationLabel = 'Configurações';
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.settings';

    public array $data = [];

    public function mount(): void
    {
        $project = Filament::getTenant();
        // dump($project->toArray());
        $this->form->fill([
            ...$project->settings,
            ...$project->only(['title', 'slug']),
        ]);
    }

    public function save(): void
    {
        $data = collect($this->form->getState())
            // ->dd() // Debug
            ->whereNotNull();
        $projectFields = ['title', 'slug'];
        $projectSettings = $data->only($projectFields)->toArray();
        $updatedSettings = $data->except($projectFields)->toArray();

        $project = Filament::getTenant();
        $project->update([
            ...$projectSettings,
            'settings' => [
                ...$project->settings ?? [],
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
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Este é o título que será mostrado na para os usuários do seu site.'),
                        TextInput::make('description')
                            ->label('Descrição')
                            ->maxLength(255),
                        Toggle::make('adult')
                            ->label('Conteúdo adulto')
                            ->helperText('Mostrar aviso aos leitores do site')
                            ->default(false)
                    ]),

                Section::make('Domínio')
                    ->description('As pessoas encontrarão seu site on-line neste endereço da Web')
                    ->aside()
                    ->schema([
                        TextInput::make('slug')
                            ->label('Endereço')
                            ->required()
                            ->alphaDash()
                            ->maxLength(24)
                            ->live(debounce: 300)
                            ->suffix(fn () => '.' . preg_replace('/^http(s)?:\/\//', '', config('app.url')))
                            ->unique('projects', 'slug', ignorable: Filament::getTenant()),
                    ]),

                Section::make('Aparência')
                    ->description('Personalize como as pessoas verão seu site')
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
                            ->label('Visível ao público')
                            ->helperText('Pemitir que qualquer pessoa com o endereço acesse seu site. Ao desativar essa opção só você poderá visualizar as páginas.'),
                        Toggle::make('indexing')
                            ->label('Visível para mecanismos de pesquisa')
                            ->helperText('Permitir que mecanismos de pesquisa encontrem seu site'),
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
