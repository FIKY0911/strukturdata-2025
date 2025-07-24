<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ProductCategory;
use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // ✅ Restrict who can access this resource
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('client'));
    }

    // ✅ Optional: hide from menu if not authorized
    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super_admin');
        $clientId = $user->client?->id;

        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Client')
                    ->required()
                    ->options(Client::with('user')->get()->pluck('user.name', 'id')->toArray())
                    ->default($clientId)
                    ->visible($isSuperAdmin)
                    ->disabled(!$isSuperAdmin),

                Forms\Components\FileUpload::make('image')
                    ->image(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('description')
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\TextInput::make('price')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('category')
                    ->options(ProductCategory::labels())
                    ->required()
                    ->disabled(fn(string $context) => $context === 'edit'),

                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->maxLength(255)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $user = Auth::user();

                if ($user->hasRole('client')) {
                    $clientId = $user->client?->id;
                    if ($clientId) {
                        $query->where('client_id', $clientId);
                    } else {
                        $query->whereRaw('1=0'); // no results
                    }
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')->sortable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('description')->searchable(),
                Tables\Columns\TextColumn::make('price')->searchable(),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('stock')->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn(Product $record) => Auth::user()->hasRole('super_admin') || $record->client_id === Auth::user()->client?->id),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasRole('super_admin')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
