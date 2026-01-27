<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuideCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'icon',
        'order',
    ];

    public function guides()
    {
        return $this->hasMany(Guide::class, 'category_id');
    }
}
