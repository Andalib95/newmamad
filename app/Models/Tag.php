<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'titre',
        'alias',
        'index',
        'follow',
    ];

    protected $casts = [
        'index' => 'boolean',
        'follow' => 'boolean',
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tag');
    }
}
