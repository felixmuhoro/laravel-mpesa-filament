<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | The navigation group label under which M-Pesa resources will appear
    | in the Filament sidebar.
    |
    */
    'navigation_group' => 'M-Pesa',

    /*
    |--------------------------------------------------------------------------
    | Navigation Sort
    |--------------------------------------------------------------------------
    |
    | Integer sort order for the navigation group. null = Filament default.
    |
    */
    'navigation_sort' => null,

    /*
    |--------------------------------------------------------------------------
    | Transaction Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model that represents an M-Pesa transaction.
    | Override this if your application uses a custom model.
    |
    */
    'transaction_model' => \FelixMuhoro\Mpesa\Models\MpesaTransaction::class,

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Display currency symbol for amounts in the admin panel.
    |
    */
    'currency_symbol' => 'KES',

    /*
    |--------------------------------------------------------------------------
    | Chart Days
    |--------------------------------------------------------------------------
    |
    | Number of days shown in the revenue line chart widget.
    |
    */
    'chart_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Latest Transactions Limit
    |--------------------------------------------------------------------------
    |
    | Number of rows shown in the Latest Transactions widget.
    |
    */
    'latest_transactions_limit' => 10,

    /*
    |--------------------------------------------------------------------------
    | Status Colors
    |--------------------------------------------------------------------------
    |
    | Mapping of transaction status values to Filament badge colors.
    | Supported: 'success', 'danger', 'warning', 'info', 'gray'
    |
    */
    'status_colors' => [
        'completed'  => 'success',
        'successful' => 'success',
        'success'    => 'success',
        'failed'     => 'danger',
        'failure'    => 'danger',
        'pending'    => 'warning',
        'processing' => 'info',
        'cancelled'  => 'gray',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exportable Columns
    |--------------------------------------------------------------------------
    |
    | Columns included in the CSV export of transactions.
    |
    */
    'export_columns' => [
        'receipt_number',
        'phone_number',
        'amount',
        'status',
        'transaction_type',
        'created_at',
    ],

];
