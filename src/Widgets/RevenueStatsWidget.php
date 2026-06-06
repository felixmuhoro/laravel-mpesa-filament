<?php

namespace FelixMuhoro\MpesaFilament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RevenueStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $model = config('mpesa-filament.transaction_model', \FelixMuhoro\Mpesa\Models\MpesaTransaction::class);

        $successStatuses = ['completed', 'successful', 'success'];

        // Today's revenue
        $todayRevenue = $model::query()
            ->whereIn(DB::raw('LOWER(status)'), $successStatuses)
            ->whereDate('created_at', today())
            ->sum('amount');

        // Yesterday for comparison
        $yesterdayRevenue = $model::query()
            ->whereIn(DB::raw('LOWER(status)'), $successStatuses)
            ->whereDate('created_at', today()->subDay())
            ->sum('amount');

        // This month
        $monthRevenue = $model::query()
            ->whereIn(DB::raw('LOWER(status)'), $successStatuses)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Last month for comparison
        $lastMonthRevenue = $model::query()
            ->whereIn(DB::raw('LOWER(status)'), $successStatuses)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');

        // Success rate (last 30 days)
        $totalLast30 = $model::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $successLast30 = $model::query()
            ->whereIn(DB::raw('LOWER(status)'), $successStatuses)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $successRate = $totalLast30 > 0
            ? round(($successLast30 / $totalLast30) * 100, 1)
            : 0;

        // Failed count (today)
        $failedToday = $model::query()
            ->whereIn(DB::raw('LOWER(status)'), ['failed', 'failure'])
            ->whereDate('created_at', today())
            ->count();

        // Revenue trend sparkline (last 7 days)
        $sparkline = collect(range(6, 0))->map(fn ($d) => (float) $model::query()
            ->whereIn(DB::raw('LOWER(status)'), $successStatuses)
            ->whereDate('created_at', today()->subDays($d))
            ->sum('amount')
        )->values()->toArray();

        $todayTrend   = $todayRevenue >= $yesterdayRevenue ? 'up' : 'down';
        $monthTrend   = $monthRevenue >= $lastMonthRevenue ? 'up' : 'down';
        $todayColor   = $todayTrend === 'up' ? 'success' : 'danger';
        $monthColor   = $monthTrend === 'up' ? 'success' : 'danger';

        return [
            Stat::make("Today's Revenue", 'KES ' . number_format($todayRevenue, 2))
                ->description($todayTrend === 'up'
                    ? 'Up from KES ' . number_format($yesterdayRevenue, 2) . ' yesterday'
                    : 'Down from KES ' . number_format($yesterdayRevenue, 2) . ' yesterday'
                )
                ->descriptionIcon($todayTrend === 'up' ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayColor)
                ->chart($sparkline),

            Stat::make('This Month', 'KES ' . number_format($monthRevenue, 2))
                ->description($monthTrend === 'up'
                    ? 'Up from KES ' . number_format($lastMonthRevenue, 2) . ' last month'
                    : 'Down from KES ' . number_format($lastMonthRevenue, 2) . ' last month'
                )
                ->descriptionIcon($monthTrend === 'up' ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthColor),

            Stat::make('Success Rate (30d)', $successRate . '%')
                ->description($successLast30 . ' of ' . $totalLast30 . ' transactions')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger')),

            Stat::make('Failed Today', (string) $failedToday)
                ->description('Failed transactions today')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($failedToday === 0 ? 'success' : 'danger'),
        ];
    }
}
