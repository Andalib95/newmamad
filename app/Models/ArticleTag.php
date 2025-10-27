<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ArticleTag extends Pivot
{
    protected $table = 'article_tag';

    public $incrementing = true;

    protected $fillable = [
        'article_id',
        'tag_id',
    ];
}
