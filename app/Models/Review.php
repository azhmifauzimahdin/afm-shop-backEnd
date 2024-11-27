<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $with = ['images', 'user'];
    protected $appends = ['date'];

    protected $hidden = [
        'user_id',
        'product_id',
        'created_at',
        'updated_at'
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->updated_at->diffForHumans()
        );
    }
}
