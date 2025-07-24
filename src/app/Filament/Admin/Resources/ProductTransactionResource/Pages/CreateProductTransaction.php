<?php

namespace App\Filament\Admin\Resources\ProductTransactionResource\Pages;

use App\Filament\Admin\Resources\ProductTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductTransaction extends CreateRecord
{
    protected static string $resource = ProductTransactionResource::class;
}
