# laravel-mpesa-filament

A production-quality **Filament v3** admin panel plugin for managing **M-Pesa** transactions in Laravel applications.

Built on top of [`felixmuhoro/laravel-mpesa`](https://github.com/felixmuhoro/laravel-mpesa), this package gives you a complete admin interface for your M-Pesa integration without writing a single line of UI code.

---

## Features

- **Transaction Resource** — searchable, filterable, sortable table of all M-Pesa transactions with date range, status, type and amount filters
- **Revenue Stats Widget** — today's revenue, monthly revenue, 30-day success rate, failed transaction count with trend indicators
- **Revenue Chart Widget** — 30-day line chart of revenue or transaction volume (switchable)
- **Latest Transactions Widget** — last N transactions at a glance
- **M-Pesa Dashboard Page** — all widgets combined on a single dedicated page
- **CSV Export** — export all or selected transactions to CSV
- **STK Push Action** — trigger an STK Push directly from the admin panel
- **Fluent plugin config** — navigation group, sort, and access control via `MpesaFilamentPlugin::make()`

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.1 |
| Laravel | ^10.0 \| ^11.0 \| ^12.0 \| ^13.0 |
| Filament | ^3.0 |
| felixmuhoro/laravel-mpesa | ^1.2 |

---

## Installation

```bash
composer require felixmuhoro/laravel-mpesa-filament
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag="mpesa-filament-config"
```

---

## Setup

Register the plugin on your Filament panel inside your `PanelProvider`:

```php
use FelixMuhoro\MpesaFilament\MpesaFilamentPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... your other panel config
        ->plugin(
            MpesaFilamentPlugin::make()
                ->navigationGroup('Finance')   // optional, default: 'M-Pesa'
                ->navigationSort(10)           // optional
                ->canViewAny(true)             // optional, default: true
        );
}
```

---

## Configuration

After publishing, edit `config/mpesa-filament.php`:

```php
return [
    'navigation_group'         => 'M-Pesa',
    'navigation_sort'          => null,

    // Override with your own model if needed
    'transaction_model'        => \FelixMuhoro\Mpesa\Models\MpesaTransaction::class,

    'currency_symbol'          => 'KES',
    'chart_days'               => 30,
    'latest_transactions_limit' => 10,

    // Map status strings to Filament badge colors
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

    // Columns included in CSV exports
    'export_columns' => [
        'receipt_number',
        'phone_number',
        'amount',
        'status',
        'transaction_type',
        'created_at',
    ],
];
```

---

## Plugin API

### `MpesaFilamentPlugin`

| Method | Description |
|---|---|
| `make()` | Create plugin instance (use in panel provider) |
| `navigationGroup(string $group)` | Set the sidebar navigation group label |
| `navigationSort(int $sort)` | Set sort order within the group |
| `canViewAny(bool $condition)` | Toggle whether any user can access the resources |
| `withTransactionResource(bool)` | Enable/disable the Transaction resource |
| `withDashboardPage(bool)` | Enable/disable the M-Pesa Dashboard page |
| `withWidgets(bool)` | Enable/disable the stats/chart/table widgets |

---

## Included Components

### Resources

#### `TransactionResource`

Full Filament resource for the `mpesa_transactions` table.

**Columns:** Receipt Number, Phone, Amount (formatted KES), Status (badge), Type (badge), Reference, Date

**Filters:**
- Date range (from / until)
- Status (multi-select)
- Transaction Type (multi-select)
- Amount range (min / max)

**Actions:**
- View transaction detail (Infolist)
- STK Push header action
- Bulk export selected rows to CSV

### Widgets

#### `RevenueStatsWidget`

Four stat cards:
1. Today's Revenue vs yesterday (with sparkline)
2. This Month's Revenue vs last month
3. 30-day Success Rate (%)
4. Failed Transactions Today

#### `RevenueChartWidget`

30-day line chart with a toggle filter between **Revenue (KES)** and **Transaction Count**.

#### `LatestTransactionsWidget`

Table of the last N transactions (configurable via `latest_transactions_limit`).

### Pages

#### `MpesaDashboardPage`

Dedicated admin page at `/mpesa-dashboard` combining all three widgets. Registered in the same navigation group as the Transaction resource.

### Actions

#### `InitiateStkPushAction`

Can be added to any table or page. Opens a modal form with:
- Phone number (with 254XXXXXXXXX validation)
- Amount (KES, 1–150,000)
- Account Reference (max 12 chars)
- Description (max 13 chars)

Calls `Mpesa::stkPush()` and shows a success or error notification.

```php
use FelixMuhoro\MpesaFilament\Actions\InitiateStkPushAction;

// In a custom table:
->actions([
    InitiateStkPushAction::make(),
])
```

### Filters

#### `DateRangeFilter`

Reusable date-range filter for any table column:

```php
use FelixMuhoro\MpesaFilament\Filters\DateRangeFilter;

->filters([
    DateRangeFilter::make('created_at'),
    DateRangeFilter::make('updated_at'),
])
```

---

## Testing

```bash
composer test
```

Tests use Pest and cover list rendering, search, all four filter types, sorting, view page, and permission helpers.

---

## Changelog

### v1.0.0
- Initial release

---

## License

MIT — see [LICENSE](LICENSE) for details.

---

## Credits

Built by [Felix Muhoro](https://felixmuhoro.dev).
