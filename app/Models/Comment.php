<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'author_name',
        'author_email',
        'author_ip',
        'author_agent',
        'body',
        'answer',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
