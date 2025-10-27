<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ArticleCategory extends Pivot
{
    protected $table = 'article_category';

    public $incrementing = true;

    protected $fillable = [
        'article_id',
        'category_id',
    ];
}
