<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

class Product extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $with = ['images'];
    protected $appends = ['price_now', 'format_price'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    protected function formatPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => Number::format($this->price, locale: 'de')
        );
    }

    protected function priceNow(): Attribute
    {
        return Attribute::make(
            get: fn() => Number::format($this->price * (100 - $this->discount) / 100, locale: 'de')
        );
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when(
            $filters['search'] ?? false,
            fn($query, $search) =>
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhere('price', 'like', '%' . $search . '%')
                ->orWhere('discount', 'like', '%' . $search . '%')
                ->orWhere('sold', 'like', '%' . $search . '%')
        );
    }
}
