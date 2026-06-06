<?php

namespace FelixMuhoro\MpesaFilament\Resources\TransactionResource\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use FelixMuhoro\MpesaFilament\Resources\TransactionResource;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $statusColors = config('mpesa-filament.status_colors', []);

        return $infolist
            ->schema([
                Section::make('Transaction Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('receipt_number')
                            ->label('M-Pesa Receipt')
                            ->copyable()
                            ->weight('bold')
                            ->placeholder('Not assigned yet'),

                        TextEntry::make('phone_number')
                            ->label('Phone Number'),

                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money('KES', locale: 'en_KE')
                            ->weight('bold'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => $statusColors[strtolower($state)] ?? 'gray')
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                        TextEntry::make('transaction_type')
                            ->label('Transaction Type')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('account_reference')
                            ->label('Account Reference'),
                    ]),

                Section::make('Result Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('result_code')
                            ->label('Result Code'),

                        TextEntry::make('result_desc')
                            ->label('Result Description')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Timestamps')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('F j, Y \a\t H:i:s'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F j, Y \a\t H:i:s')
                            ->since(),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Transactions')
                ->url(TransactionResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
