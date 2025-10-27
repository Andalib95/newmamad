<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelHasRole extends Model
{
    use HasFactory;

    protected $table = 'model_has_roles';

    protected $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];

    public $timestamps = false;

    protected $primaryKey = null;

    public $incrementing = false;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}
