<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $with = ['messages', 'user', 'admin'];
    protected $appends = ['read_user', 'read_admin'];

    protected $hidden = [
        'user_id',
        'admin_id',
        'updated_at',
        'created_at'
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
    public function messagesUnread(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function readUser(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->messages()->where('status', 0)->where('sent_by', 'admin')->get()->count()
        );
    }

    public function readAdmin(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->messages()->where('status', 0)->where('sent_by', 'user')->get()->count()
        );
    }
}
