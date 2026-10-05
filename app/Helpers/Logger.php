<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class Logger {
    public static function log($action, $description = null) {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {}
    }
}
