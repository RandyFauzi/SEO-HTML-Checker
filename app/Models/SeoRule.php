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
        'target_selector',
        'attribute',
        'operator',
        'expected_value',
        'rule_type',
        'min_value',
        'max_value',
        'regex_pattern',
        'severity',
        'is_active',
        'sort_order',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rule_type' => \App\Enums\RuleType::class,
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }
}
