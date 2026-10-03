<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\AI\AiClientInterface::class, function ($app) {
            $provider = config('services.ai_provider', 'openai');
            
            if ($provider === 'gemini') {
                return new \App\Services\AI\GeminiClientService();
            }
            
            return new \App\Services\AI\OpenAIClientService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
