<?php

namespace App\Filament\Admin\Resources\ProductTransactionResource\Pages;

use App\Filament\Admin\Resources\ProductTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductTransactions extends ListRecords
{
    protected static string $resource = ProductTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
