<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\HasHashId;

class AuditRun extends Model
{
    use HasHashId;

    protected $fillable = ['user_id', 'total_urls'];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results()
    {
        return $this->hasMany(AuditResult::class);
    }
}
