<?php

namespace FelixMuhoro\MpesaFilament\Resources\TransactionResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use FelixMuhoro\MpesaFilament\Resources\TransactionResource;
use Illuminate\Support\Facades\Response;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_all_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (): \Symfony\Component\HttpFoundation\StreamedResponse {
                    $model   = TransactionResource::getModel();
                    $columns = config('mpesa-filament.export_columns', [
                        'receipt_number',
                        'phone_number',
                        'amount',
                        'status',
                        'transaction_type',
                        'created_at',
                    ]);

                    return response()->streamDownload(function () use ($model, $columns) {
                        echo implode(',', array_map('ucwords', array_map(
                            fn ($c) => str_replace('_', ' ', $c),
                            $columns
                        ))) . "\n";

                        $model::query()
                            ->latest()
                            ->chunk(500, function ($records) use ($columns) {
                                foreach ($records as $record) {
                                    $row = array_map(
                                        fn ($col) => '"' . str_replace('"', '""', (string) ($record->{$col} ?? '')) . '"',
                                        $columns
                                    );
                                    echo implode(',', $row) . "\n";
                                }
                            });
                    }, 'mpesa-transactions-' . now()->format('Y-m-d-His') . '.csv', [
                        'Content-Type' => 'text/csv',
                    ]);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \FelixMuhoro\MpesaFilament\Widgets\RevenueStatsWidget::class,
        ];
    }
}
