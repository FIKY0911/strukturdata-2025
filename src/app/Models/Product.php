<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';
    protected $guarded = ['id'];

    protected $casts = [
        'category' => ProductCategory::class,
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
