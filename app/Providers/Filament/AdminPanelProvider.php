<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->spa()
            ->login()
            ->registration()
            ->passwordReset()
            ->profile()
            ->tenant(
                \App\Models\Project::class,
                slugAttribute: 'slug',
            )
            ->tenantRegistration(\App\Filament\Pages\Tenancy\RegisterProject::class)
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function register(): void
    {
        Filament::registerPanel(
            $this->panel(Panel::make()),
        );

        FilamentView::registerRenderHook('panels::tenant-menu.after', function (): string {
            return Blade::render("@livewire('public-toggle')");
        });

        FilamentView::registerRenderHook('panels::user-menu.before', function (): string {
            return Blade::render("
                <x-filament::button
                    tag='a'
                    href='https://filamentphp.com'
                    target='_blank'
                    color='gray'
                    icon='heroicon-m-eye'
                    outlined
                    tooltip='Ver blog em outra guia'
                    style='box-shadow:none'
                >
                    Visualizar
                </x-filament::button>
            ");
        });

        // FilamentView::registerRenderHook('panels::topbar.start', function (): string {
        //     return Blade::render("<x-filament::tenant-menu/>");
        // });
    }
}
