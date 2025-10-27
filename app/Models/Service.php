<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'title',
        'alias',
        'keywords',
        'description',
        'intro_text',
        'body',
        'publish',
        'index',
        'follow',
    ];

    protected $casts = [
        'publish' => 'boolean',
        'index' => 'boolean',
        'follow' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
