<?php

namespace App\Filament\Pages;

use App\Enums\PrimaryColor;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Forms\Form;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\{Section, FileUpload, ColorPicker, Grid, Select};
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;

class AppearancePage extends Page
{
    use InteractsWithForms, InteractsWithFormActions;

    protected static ?string $slug = 'appearance';
    protected static ?string $title = 'Aparência';
    protected ?string $subheading = 'Personalize como as pessoas verão seu site';
    protected static ?string $navigationLabel = 'Aparência';
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.form-page';

    public array $data = [];

    public function mount(): void
    {
        $project = Filament::getTenant();
        $this->form->fill($project->settings);
    }

    public function save(): void
    {
        $project = Filament::getTenant();
        $project->updateSettings($this->form->getState());

        Notification::make()
            ->title('Alterações foram salvas!')
            ->success()
            ->seconds(10)
            ->actions([
                NotificationAction::make('view')
                    ->label('Visualizar')
                    ->url(route('blog-homepage', [$project->slug]))
                    ->icon('heroicon-o-eye')
                    ->openUrlInNewTab(),
            ])
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Logos')
                    ->icon('heroicon-m-photo')
                    ->columns(2)
                    ->schema([
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

                Section::make('Cores')
                    ->icon('heroicon-m-swatch')
                    ->schema([
                        Select::make('theme')
                            ->label('Esquema de cores')
                            ->options([
                                'dark' => 'Escuro',
                                'light' => 'Claro'
                            ])
                            ->selectablePlaceholder(false),
                        Select::make('primary_color')
                            ->label('Cor primária')
                            ->helperText('Será usado nos links e botões')
                            ->native(false)
                            ->allowHtml()
                            ->selectablePlaceholder(false)
                            ->default(PrimaryColor::DEFAULT)
                            ->options(
                                collect(PrimaryColor::cases())->mapWithKeys(fn ($case) => [
                                    $case->value => "<span class='flex items-center gap-x-4'>
                                        <span class='rounded-full w-4 h-4' style='background:rgb(" . $case->getColor()[600] . ")'></span>
                                        <span>" . str($case->value)->title() . '</span>
                                        </span>',
                                ]),
                            ),
                        ColorPicker::make('background_color')
                            ->label('Plano de fundo'),
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
