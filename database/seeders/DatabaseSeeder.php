<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin creation has been moved to an Artisan command for security.
        // Run: php artisan make:admin

        $this->call(SeoRuleSeeder::class);
    }
}
