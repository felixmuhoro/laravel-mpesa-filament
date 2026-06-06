<?php

namespace FelixMuhoro\MpesaFilament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use FelixMuhoro\MpesaFilament\Pages\MpesaDashboardPage;
use FelixMuhoro\MpesaFilament\Resources\TransactionResource;
use FelixMuhoro\MpesaFilament\Widgets\LatestTransactionsWidget;
use FelixMuhoro\MpesaFilament\Widgets\RevenueChartWidget;
use FelixMuhoro\MpesaFilament\Widgets\RevenueStatsWidget;

class MpesaFilamentPlugin implements Plugin
{
    protected string $navigationGroup = 'M-Pesa';

    protected ?int $navigationSort = null;

    protected bool $canViewAny = true;

    protected bool $hasTransactionResource = true;

    protected bool $hasDashboardPage = true;

    protected bool $hasWidgets = true;

    // -----------------------------------------------------------------------
    // Factory
    // -----------------------------------------------------------------------

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    // -----------------------------------------------------------------------
    // FilamentPlugin contract
    // -----------------------------------------------------------------------

    public function getId(): string
    {
        return 'mpesa-filament';
    }

    public function register(Panel $panel): void
    {
        $resources = [];
        $pages     = [];
        $widgets   = [];

        if ($this->hasTransactionResource) {
            $resources[] = TransactionResource::class;
        }

        if ($this->hasDashboardPage) {
            $pages[] = MpesaDashboardPage::class;
        }

        if ($this->hasWidgets) {
            $widgets[] = RevenueStatsWidget::class;
            $widgets[] = RevenueChartWidget::class;
            $widgets[] = LatestTransactionsWidget::class;
        }

        $panel
            ->resources($resources)
            ->pages($pages)
            ->widgets($widgets);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    // -----------------------------------------------------------------------
    // Fluent configuration
    // -----------------------------------------------------------------------

    public function navigationGroup(string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): string
    {
        return $this->navigationGroup;
    }

    public function navigationSort(int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }

    public function canViewAny(bool $condition = true): static
    {
        $this->canViewAny = $condition;

        return $this;
    }

    public function getCanViewAny(): bool
    {
        return $this->canViewAny;
    }

    public function withTransactionResource(bool $condition = true): static
    {
        $this->hasTransactionResource = $condition;

        return $this;
    }

    public function withDashboardPage(bool $condition = true): static
    {
        $this->hasDashboardPage = $condition;

        return $this;
    }

    public function withWidgets(bool $condition = true): static
    {
        $this->hasWidgets = $condition;

        return $this;
    }
}
