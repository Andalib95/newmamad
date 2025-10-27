<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'main_category_id',
        'title',
        'alias',
        'keywords',
        'description',
        'intro_text',
        'body',
        'publish',
        'comment_status',
        'comment_count',
        'hits',
        'index',
        'has_toc',
        'follow',
    ];

    protected $casts = [
        'publish' => 'boolean',
        'comment_status' => 'boolean',
        'index' => 'boolean',
        'has_toc' => 'boolean',
        'follow' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mainCategory()
    {
        return $this->belongsTo(Category::class, 'main_category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
