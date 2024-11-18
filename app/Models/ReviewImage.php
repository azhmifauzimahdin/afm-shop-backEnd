<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewImage extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $appends = ['image_url'];

    protected $hidden = [
        'review_id',
        'name',
        'created_at',
        'updated_at'
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(get: function () {
            if (!is_null($this->name)) {
                return asset('storage/images/reviews/' . $this->name);
            }
            return $this->name;
        });
    }
}
