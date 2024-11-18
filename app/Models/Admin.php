<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids;

    protected $guard = "admin";
    protected $guarded = ["id"];
    protected $appends = ['image_url'];

    protected $hidden = [
        'password',
        'image',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(get: function () {
            if (!is_null($this->image)) {
                return asset('storage/images/users/' . $this->image);
            }
            return $this->image;
        });
    }
}
