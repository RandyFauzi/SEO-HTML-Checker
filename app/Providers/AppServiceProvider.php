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
            $provider = env('AI_PROVIDER', 'openai');
            
            $openai = new \App\Services\AI\OpenAIClientService();
            $gemini = new \App\Services\AI\GeminiClientService();
            
            if ($provider === 'gemini') {
                return new \App\Services\AI\FallbackAiClientService($gemini, $openai);
            }
            
            // Default to openai, fallback to gemini
            return new \App\Services\AI\FallbackAiClientService($openai, $gemini);
        });
    }

    /**
     * Bootstrap any application services.
     */
        public function boot(): void
    {
        // Force HTTPS if using ngrok or in production
        if (str_contains(request()->getHost(), 'ngrok') || $this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
