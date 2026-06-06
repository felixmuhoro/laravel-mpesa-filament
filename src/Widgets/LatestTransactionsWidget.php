<?php

namespace FelixMuhoro\MpesaFilament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use FelixMuhoro\MpesaFilament\Resources\TransactionResource;

class LatestTransactionsWidget extends TableWidget
{
    protected static ?string $heading = 'Latest Transactions';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $model = config('mpesa-filament.transaction_model', \FelixMuhoro\Mpesa\Models\MpesaTransaction::class);
        $limit = (int) config('mpesa-filament.latest_transactions_limit', 10);

        return $model::query()->latest()->limit($limit);
    }

    public function table(Table $table): Table
    {
        $statusColors = config('mpesa-filament.status_colors', []);

        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('Receipt')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Pending'),

                TextColumn::make('phone_number')
                    ->label('Phone'),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('KES', locale: 'en_KE')
                    ->alignEnd()
                    ->weight('semibold'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $statusColors[strtolower($state)] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => TransactionResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
