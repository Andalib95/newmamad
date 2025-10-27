<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'alias',
        'email',
        'password',
        'index',
        'follow',
        'is_admin',
        'super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'index' => 'boolean',
            'follow' => 'boolean',
            'is_admin' => 'boolean',
            'super_admin' => 'boolean',
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
