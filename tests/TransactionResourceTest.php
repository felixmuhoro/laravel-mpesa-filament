<?php

use FelixMuhoro\MpesaFilament\Resources\TransactionResource;
use FelixMuhoro\MpesaFilament\Resources\TransactionResource\Pages\ListTransactions;
use FelixMuhoro\MpesaFilament\Resources\TransactionResource\Pages\ViewTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeTransaction(array $overrides = []): \Illuminate\Database\Eloquent\Model
{
    $model = config('mpesa-filament.transaction_model');

    return $model::factory()->create(array_merge([
        'receipt_number'   => 'LHG' . fake()->numerify('########'),
        'phone_number'     => '254712' . fake()->numerify('######'),
        'amount'           => fake()->randomFloat(2, 10, 10000),
        'status'           => 'completed',
        'transaction_type' => 'CustomerPayBillOnline',
        'account_reference' => 'TestRef',
    ], $overrides));
}

// ---------------------------------------------------------------------------
// Resource metadata
// ---------------------------------------------------------------------------

it('has the correct resource model', function () {
    expect(TransactionResource::getModel())
        ->toBe(config('mpesa-filament.transaction_model'));
});

it('has the correct navigation group from plugin config', function () {
    expect(TransactionResource::getNavigationGroup())
        ->toBeString();
});

// ---------------------------------------------------------------------------
// List page renders
// ---------------------------------------------------------------------------

it('can render the list transactions page', function () {
    $this->get(TransactionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list transactions', function () {
    $transactions = collect(range(1, 5))->map(fn () => makeTransaction());

    \Filament\Tests\Livewire\livewire(ListTransactions::class)
        ->assertCanSeeTableRecords($transactions);
});

it('can search transactions by receipt number', function () {
    $target = makeTransaction(['receipt_number' => 'LHGTEST12345']);
    $other  = makeTransaction(['receipt_number' => 'LHGOTHER999']);

    \Filament\Tests\Livewire\livewire(ListTransactions::class)
        ->searchTable('LHGTEST12345')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can search transactions by phone number', function () {
    $target = makeTransaction(['phone_number' => '254700000001']);
    $other  = makeTransaction(['phone_number' => '254700000002']);

    \Filament\Tests\Livewire\livewire(ListTransactions::class)
        ->searchTable('254700000001')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});

// ---------------------------------------------------------------------------
// Filters
// ---------------------------------------------------------------------------

it('can filter transactions by status', function () {
    $completed = makeTransaction(['status' => 'completed']);
    $failed    = makeTransaction(['status' => 'failed']);

    \Filament\Tests\Livewire\livewire(ListTransactions::class)
        ->filterTable('status', ['completed'])
        ->assertCanSeeTableRecords([$completed])
        ->assertCanNotSeeTableRecords([$failed]);
});

it('can filter transactions by date range', function () {
    $old   = makeTransaction(['created_at' => now()->subDays(10)]);
    $fresh = makeTransaction(['created_at' => now()]);

    \Filament\Tests\Livewire\livewire(ListTransactions::class)
        ->filterTable('created_at', [
            'from'  => now()->subDay()->toDateString(),
            'until' => now()->toDateString(),
        ])
        ->assertCanSeeTableRecords([$fresh])
        ->assertCanNotSeeTableRecords([$old]);
});

it('can filter transactions by amount range', function () {
    $small = makeTransaction(['amount' => 50]);
    $large = makeTransaction(['amount' => 5000]);

    \Filament\Tests\Livewire\livewire(ListTransactions::class)
        ->filterTable('amount_range', ['amount_from' => 1000, 'amount_to' => null])
        ->assertCanSeeTableRecords([$large])
        ->assertCanNotSeeTableRecords([$small]);
});

// ---------------------------------------------------------------------------
// View page
// ---------------------------------------------------------------------------

it('can render the view transaction page', function () {
    $tx = makeTransaction();

    $this->get(TransactionResource::getUrl('view', ['record' => $tx]))
        ->assertSuccessful();
});

it('can view a transaction record', function () {
    $tx = makeTransaction([
        'receipt_number' => 'LHGVIEWTEST01',
        'phone_number'   => '254799888777',
        'amount'         => 1234.56,
    ]);

    \Filament\Tests\Livewire\livewire(ViewTransaction::class, ['record' => $tx->getRouteKey()])
        ->assertSee('LHGVIEWTEST01')
        ->assertSee('254799888777');
});

// ---------------------------------------------------------------------------
// Sorting
// ---------------------------------------------------------------------------

it('can sort transactions by amount', function () {
    $low  = makeTransaction(['amount' => 100]);
    $high = makeTransaction(['amount' => 9999]);

    \Filament\Tests\Livewire\livewire(ListTransactions::class)
        ->sortTable('amount', 'desc')
        ->assertCanSeeTableRecords([$high, $low], inOrder: true);
});

// ---------------------------------------------------------------------------
// Permissions
// ---------------------------------------------------------------------------

it('returns the canViewAny value from the plugin', function () {
    expect(TransactionResource::canViewAny())->toBeBool();
});
