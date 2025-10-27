<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'order',
        'titre',
        'url',
        'publish',
    ];

    protected $casts = [
        'publish' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(MobileMenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MobileMenuItem::class, 'parent_id')->orderBy('order');
    }
}
