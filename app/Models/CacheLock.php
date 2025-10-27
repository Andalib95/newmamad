<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CacheLock extends Model
{
    use HasFactory;

    protected $table = 'cache_locks';

    protected $fillable = [
        'key',
        'owner',
        'expiration',
    ];

    public $timestamps = false;

    protected $primaryKey = 'key';

    public $incrementing = false;
}
