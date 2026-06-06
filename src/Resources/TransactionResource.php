<?php

namespace FelixMuhoro\MpesaFilament\Resources;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use FelixMuhoro\MpesaFilament\Actions\InitiateStkPushAction;
use FelixMuhoro\MpesaFilament\Filters\DateRangeFilter;
use FelixMuhoro\MpesaFilament\MpesaFilamentPlugin;
use FelixMuhoro\MpesaFilament\Resources\TransactionResource\Pages;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $recordTitleAttribute = 'receipt_number';

    // -----------------------------------------------------------------------
    // Meta
    // -----------------------------------------------------------------------

    public static function getModel(): string
    {
        return config('mpesa-filament.transaction_model', \FelixMuhoro\Mpesa\Models\MpesaTransaction::class);
    }

    public static function getLabel(): string
    {
        return 'Transaction';
    }

    public static function getPluralLabel(): string
    {
        return 'Transactions';
    }

    public static function getNavigationGroup(): ?string
    {
        return MpesaFilamentPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return MpesaFilamentPlugin::get()->getNavigationSort();
    }

    public static function canViewAny(): bool
    {
        return MpesaFilamentPlugin::get()->getCanViewAny();
    }

    // -----------------------------------------------------------------------
    // Form (view-only; no create/edit in this plugin)
    // -----------------------------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Transaction Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('receipt_number')
                            ->label('M-Pesa Receipt')
                            ->disabled(),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->disabled(),

                        TextInput::make('amount')
                            ->label('Amount (KES)')
                            ->disabled()
                            ->prefix('KES'),

                        TextInput::make('status')
                            ->label('Status')
                            ->disabled(),

                        TextInput::make('transaction_type')
                            ->label('Type')
                            ->disabled(),

                        TextInput::make('account_reference')
                            ->label('Account Reference')
                            ->disabled(),
                    ]),

                Section::make('Metadata')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled(),

                        DateTimePicker::make('updated_at')
                            ->label('Last Updated')
                            ->disabled(),

                        TextInput::make('result_desc')
                            ->label('Result Description')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // -----------------------------------------------------------------------
    // Table
    // -----------------------------------------------------------------------

    public static function table(Table $table): Table
    {
        $statusColors = config('mpesa-filament.status_colors', []);

        return $table
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('Receipt')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Receipt number copied')
                    ->weight('semibold')
                    ->placeholder('Pending'),

                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('KES', locale: 'en_KE')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $statusColors[strtolower($state)] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('account_reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record): string => $record->created_at?->format('Y-m-d H:i:s') ?? ''),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                DateRangeFilter::make('created_at'),

                SelectFilter::make('status')
                    ->options(fn (): array => static::getModel()::query()
                        ->distinct()
                        ->orderBy('status')
                        ->pluck('status', 'status')
                        ->mapWithKeys(fn ($v) => [$v => ucfirst($v)])
                        ->toArray()
                    )
                    ->multiple()
                    ->label('Status'),

                SelectFilter::make('transaction_type')
                    ->options(fn (): array => static::getModel()::query()
                        ->distinct()
                        ->whereNotNull('transaction_type')
                        ->orderBy('transaction_type')
                        ->pluck('transaction_type', 'transaction_type')
                        ->mapWithKeys(fn ($v) => [$v => $v])
                        ->toArray()
                    )
                    ->multiple()
                    ->label('Type'),

                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('amount_from')
                            ->label('Min Amount')
                            ->numeric()
                            ->prefix('KES'),

                        \Filament\Forms\Components\TextInput::make('amount_to')
                            ->label('Max Amount')
                            ->numeric()
                            ->prefix('KES'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['amount_from'] ?? null, fn (Builder $q, $v) => $q->where('amount', '>=', $v))
                            ->when($data['amount_to'] ?? null, fn (Builder $q, $v) => $q->where('amount', '<=', $v));
                    })
                    ->label('Amount Range'),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([
                InitiateStkPushAction::make('header_stk_push'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Selected (CSV)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): \Symfony\Component\HttpFoundation\StreamedResponse {
                            return response()->streamDownload(function () use ($records) {
                                $columns = config('mpesa-filament.export_columns', []);
                                echo implode(',', $columns) . "\n";
                                foreach ($records as $record) {
                                    $row = array_map(
                                        fn ($col) => '"' . str_replace('"', '""', (string) ($record->{$col} ?? '')) . '"',
                                        $columns
                                    );
                                    echo implode(',', $row) . "\n";
                                }
                            }, 'mpesa-transactions-' . now()->format('Y-m-d') . '.csv');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->striped()
            ->persistFiltersInSession()
            ->persistSortInSession();
    }

    // -----------------------------------------------------------------------
    // Pages
    // -----------------------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view'  => Pages\ViewTransaction::route('/{record}'),
        ];
    }

    // -----------------------------------------------------------------------
    // Global Search
    // -----------------------------------------------------------------------

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Phone'  => $record->phone_number ?? '—',
            'Amount' => 'KES ' . number_format($record->amount, 2),
            'Status' => ucfirst($record->status ?? ''),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->latest();
    }
}
