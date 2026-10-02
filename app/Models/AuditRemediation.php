<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditRemediation extends Model
{
    protected $fillable = [
        'audit_run_id',
        'original_html_path',
        'fixed_html_path',
        'ai_tokens_used',
        'remediation_log',
    ];

    protected $casts = [
        'remediation_log' => 'array',
    ];

    public function auditRun()
    {
        return $this->belongsTo(AuditRun::class);
    }
}
