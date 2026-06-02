<?php

namespace App\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('web')
            ->colors([
                'primary' => '#4f46e5',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationItems([
                NavigationItem::make('Dashboard')
                    ->icon('heroicon-o-home')
                    ->url('/admin'),
            ])
            ->navigationGroups([
                NavigationGroup::make('Clients')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('Infrastructure')
                    ->icon('heroicon-o-server'),
                NavigationGroup::make('Configuration')
                    ->icon('heroicon-o-cog-6-tooth'),
            ]);
    }
}
