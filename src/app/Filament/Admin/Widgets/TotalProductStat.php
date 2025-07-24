<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Auth;

class TotalProductStat extends BaseWidget
{
    protected ?string $heading = 'Statistik Produk';

    protected function getCards(): array
    {
        $user = Auth::user();

        $query = Product::query();

        // 🔒 Jika user memiliki peran "client", filter berdasarkan client_id
        if ($user->hasRole('user')) {
            $clientId = $user->client?->id;

            if ($clientId) {
                $query->where('client_id', $clientId);
            } else {
                // Jika tidak punya client terkait, tampilkan kosong
                $query->whereRaw('1=0'); // hasil kosong
            }
        }

        $filtered = clone $query;

        return [
            Card::make('Total Produk', (clone $filtered)->count())
                ->description('Jumlah produk yang ditampilkan')
                ->color('info')
                ->icon('heroicon-o-cube'),

            Card::make('Total Stok', (clone $filtered)->sum('stock'))
                ->description('Total stok')
                ->color('success')
                ->icon('heroicon-o-archive-box'),

            Card::make('Stok Kosong', (clone $filtered)->where('stock', 0)->count())
                ->description('Jumlah produk stok kosong')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
