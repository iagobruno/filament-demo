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

class SettingsPage extends Page
{
    use InteractsWithForms, InteractsWithFormActions;

    protected static ?string $slug = 'settings';
    protected static ?string $title = 'Configurações';
    protected ?string $subheading = 'Gerencie as configurações do seu projeto';
    protected static ?string $navigationLabel = 'Configurações';
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.form-page';

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
        $project = Filament::getTenant();
        $project->updateSettings($this->form->getState());

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
                    ->icon('heroicon-m-identification')
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
                    ->icon('heroicon-m-globe-alt')
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

                Section::make('Privacidade')
                    ->description('Configurações de segurança')
                    ->icon('heroicon-m-lock-closed')
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

    public function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Salvar alterações')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
