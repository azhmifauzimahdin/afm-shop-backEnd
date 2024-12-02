<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $appends = ['date', 'time'];

    protected $hidden = [
        'chat_id',
        'update_at'
    ];

    public function message(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Crypt::decrypt($value)
        );
    }

    public function date(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->created_at->isoFormat('D MMM YYYY')
        );
    }

    public function time(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->created_at->format('H:i')
        );
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
