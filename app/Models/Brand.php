<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['user_id', 'brand_name', 'gtag_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
