<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'order',
        'title',
        'titre',
        'alias',
        'keywords',
        'description',
        'body',
        'publish',
        'blog',
        'index',
        'follow',
    ];

    protected $casts = [
        'publish' => 'boolean',
        'blog' => 'boolean',
        'index' => 'boolean',
        'follow' => 'boolean',
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_category');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
