<?php

namespace FelixMuhoro\MpesaFilament\Filters;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DateRangeFilter extends Filter
{
    public static function make(string  = 'created_at'): static
    {
        return parent::make()
            ->form([
                DatePicker::make('from')
                    ->label('From')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->maxDate(now()),

                DatePicker::make('until')
                    ->label('Until')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->maxDate(now()),
            ])
            ->query(function (Builder , array ) use (): Builder {
                return 
                    ->when(
                        ["from"] ?? null,
                        fn (Builder , string ): Builder => ->whereDate(
                            ,
                            ">=",
                            Carbon::parse()->startOfDay()
                        )
                    )
                    ->when(
                        ["until"] ?? null,
                        fn (Builder , string ): Builder => ->whereDate(
                            ,
                            "<=",
                            Carbon::parse()->endOfDay()
                        )
                    );
            })
            ->indicateUsing(function (array ): array {
                 = [];

                if (["from"] ?? null) {
                    [] = "From " . Carbon::parse(["from"])->toFormattedDateString();
                }

                if (["until"] ?? null) {
                    [] = "Until " . Carbon::parse(["until"])->toFormattedDateString();
                }

                return ;
            });
    }
}
