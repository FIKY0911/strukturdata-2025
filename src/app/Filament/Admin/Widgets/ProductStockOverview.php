<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ProductCategory;
use App\Models\Client;
use App\Models\Product;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductStockOverview extends BaseWidget
{
    protected static ?string $heading = 'Stok Produk';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    // ✅ Query utama tabel
    protected function getTableQuery(): Builder
    {
        $query = Product::query()->with('client.user');

        $user = Auth::user();

        // Jika role client, filter berdasarkan client_id
        if ($user->hasRole('user')) {
            $clientId = $user->client?->id;

            if ($clientId) {
                $query->where('client_id', $clientId);
            } else {
                $query->whereRaw('1 = 0'); // hasil kosong jika tidak punya client
            }
        }

        return $query;
    }

    // ✅ Kartu informasi yang mengikuti filter
    protected function getCards(): array
    {
        $query = $this->getFilteredTableQuery();

        return [
            Card::make('Total Produk', (clone $query)->count())
                ->description('Jumlah seluruh produk yang difilter')
                ->color('info')
                ->icon('heroicon-o-cube'),

            Card::make('Total Stok', (clone $query)->sum('stock'))
                ->description('Akumulasi seluruh stok yang difilter')
                ->color('success')
                ->icon('heroicon-o-archive-box'),

            Card::make('Stok Kosong', (clone $query)->where('stock', 0)->count())
                ->description('Produk tanpa stok')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }

    // ✅ Kolom tabel
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Produk')
                ->searchable()
                ->url(fn(Product $record) => route('filament.admin.resources.products.edit', ['record' => $record]))
                ->openUrlInNewTab(),

            Tables\Columns\TextColumn::make('client.user.name')
                ->label('Client'),

            Tables\Columns\TextColumn::make('category')
                ->label('Kategori')
                ->formatStateUsing(fn($state) => is_string($state)
                    ? ProductCategory::from($state)->label()
                    : $state->label()),

            Tables\Columns\TextColumn::make('stock')
                ->label('Stok')
                ->formatStateUsing(fn($state) => $state == 0
                    ? 'Stock Kosong'
                    : "{$state} unit")
                ->color(fn($state) => $state == 0 ? 'danger' : 'success')
                ->sortable(),
        ];
    }

    // ✅ Aksi per baris
    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('restock')
                ->label('Restock')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('amount')
                        ->label('Jumlah Tambahan')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ])
                ->action(function (array $data, Product $record): void {
                    $record->increment('stock', $data['amount']);

                    Notification::make()
                        ->title("Stok {$record->name} ditambahkan {$data['amount']} unit")
                        ->success()
                        ->send();
                }),
        ];
    }

    // ✅ Filter tabel
    protected function getTableFilters(): array
    {
        $user = Auth::user();

        // Filter client hanya untuk super_admin
        $clientFilter = [];

        if ($user->hasRole('super_admin')) {
            $clientFilter[] = SelectFilter::make('client_id')
                ->label('Filter Client')
                ->options(
                    Client::with('user')->get()->pluck('user.name', 'id')->toArray()
                )
                ->attribute('client_id');
        }

        return array_merge($clientFilter, [
            SelectFilter::make('category')
                ->label('Kategori')
                ->options(ProductCategory::labels())
                ->attribute('category'),
        ]);
    }
}
