<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoRule extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'rule_type',
        'config',
        'issue_message',
        'reason_template',
        'recommendation',
        'severity',
        'is_active',
        'sort_order',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'rule_type' => \App\Enums\RuleType::class,
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }
}
