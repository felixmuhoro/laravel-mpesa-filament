<?php

namespace FelixMuhoro\MpesaFilament\Pages;

use Filament\Pages\Page;
use FelixMuhoro\MpesaFilament\MpesaFilamentPlugin;
use FelixMuhoro\MpesaFilament\Widgets\LatestTransactionsWidget;
use FelixMuhoro\MpesaFilament\Widgets\RevenueChartWidget;
use FelixMuhoro\MpesaFilament\Widgets\RevenueStatsWidget;

class MpesaDashboardPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'mpesa-filament::pages.mpesa-dashboard';

    protected static ?string $title = 'M-Pesa Dashboard';

    protected static ?string $slug = 'mpesa-dashboard';

    public static function getNavigationLabel(): string
    {
        return 'M-Pesa Dashboard';
    }

    public static function getNavigationGroup(): ?string
    {
        return MpesaFilamentPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        // Dashboard always comes first within the group
        $pluginSort = MpesaFilamentPlugin::get()->getNavigationSort();

        return $pluginSort !== null ? $pluginSort - 1 : null;
    }

    public static function canAccess(): bool
    {
        return MpesaFilamentPlugin::get()->getCanViewAny();
    }

    public function getWidgets(): array
    {
        return [
            RevenueStatsWidget::class,
            RevenueChartWidget::class,
            LatestTransactionsWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 1;
    }
}
