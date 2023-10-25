<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Forms\Components\{Checkbox, Fieldset, Placeholder, Section, Tabs, TextInput};
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions;
use Filament\Notifications\Notification;

class AccountSettingsPage extends Page
{
    use InteractsWithForms, InteractsWithFormActions;

    protected static ?string $slug = 'account';
    protected static ?string $title = 'Configurações da conta';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.account-settings-page';

    public array $data = [];

    public function save()
    {
        Notification::make()
            ->title('As alterações foram salvas')
            ->success()
            ->seconds(10)
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Tabs::make()
                    ->contained(true)
                    ->tabs([
                        Tabs\Tab::make('Conta')
                            ->icon('heroicon-m-identification')
                            ->schema($this->getAccountFormSchema()),

                        Tabs\Tab::make('Pagamento')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                // ...
                            ]),
                        Tabs\Tab::make('Notificações')
                            ->icon('heroicon-m-bell')
                            ->schema($this->getNotificationsFormSchema()),
                        Tabs\Tab::make('Segurança')
                            ->icon('heroicon-m-lock-closed')
                            ->schema([
                                Placeholder::make('')
                                    ->content(AccountSettingsPage::getUrl())
                            ]),
                    ])
                    ->persistTabInQueryString()
            ]);
    }

    public function getAccountFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Nome:')
                ->required(),
            TextInput::make('email')
                ->label('Email:')
                ->email()
                ->required(),
        ];
    }

    public function getNotificationsFormSchema(): array
    {
        return [
            Fieldset::make('Email')->columns(1)->schema([
                Checkbox::make('reaction')
                    ->label('Quando alguem reajir a um lançamento'),
                Checkbox::make('product_updates')
                    ->label('Receber atualizações e novidades sobre o ' . config('app.name')),
            ]),
            Fieldset::make('Navegador')->columns(1)->schema([
                Checkbox::make('reaction')
                    ->label('Quando alguem reajir a um lançamento'),
                Checkbox::make('product_updates')
                    ->label('Receber atualizações e novidades sobre o ' . config('app.name')),
            ]),
            Fieldset::make('SMS')->columns(1)->schema([
                Checkbox::make('reaction')
                    ->label('Quando alguem reajir a um lançamento'),
                Checkbox::make('product_updates')
                    ->label('Receber atualizações e novidades sobre o ' . config('app.name')),
            ]),
        ];
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
