<?php

namespace FelixMuhoro\MpesaFilament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue (Last 30 Days)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'revenue';

    protected function getFilters(): ?array
    {
        return [
            'revenue'      => 'Revenue (KES)',
            'transactions' => 'Transaction Count',
        ];
    }

    protected function getData(): array
    {
        $model          = config('mpesa-filament.transaction_model', \FelixMuhoro\Mpesa\Models\MpesaTransaction::class);
        $days           = (int) config('mpesa-filament.chart_days', 30);
        $successStatuses = ['completed', 'successful', 'success'];

        $labels  = [];
        $data    = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('M j');

            if ($this->filter === 'transactions') {
                $data[] = (int) $model::query()
                    ->whereDate('created_at', $date)
                    ->count();
            } else {
                $data[] = (float) $model::query()
                    ->whereIn(DB::raw('LOWER(status)'), $successStatuses)
                    ->whereDate('created_at', $date)
                    ->sum('amount');
            }
        }

        return [
            'datasets' => [
                [
                    'label'                     => $this->filter === 'transactions' ? 'Transactions' : 'Revenue (KES)',
                    'data'                      => $data,
                    'fill'                      => true,
                    'borderColor'               => 'rgb(16, 185, 129)',
                    'backgroundColor'           => 'rgba(16, 185, 129, 0.1)',
                    'tension'                   => 0.4,
                    'pointBackgroundColor'       => 'rgb(16, 185, 129)',
                    'pointBorderColor'           => '#fff',
                    'pointBorderWidth'           => 2,
                    'pointRadius'                => 4,
                    'pointHoverRadius'           => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => [
                        'callback' => $this->filter === 'revenue'
                            ? 'function(value) { return "KES " + value.toLocaleString(); }'
                            : null,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => $this->filter === 'revenue'
                            ? 'function(ctx) { return "KES " + ctx.raw.toLocaleString(undefined, {minimumFractionDigits: 2}); }'
                            : null,
                    ],
                ],
            ],
        ];
    }
}
