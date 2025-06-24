<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Providers;

use Illuminate\Support\ServiceProvider;
use KrasnikovDev\LaravelSwaggerAttributes\Commands\LaravelSwaggerAttributesGenerate;

class LaravelSwaggerAttributesProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../../config/laravel-swagger-attributes.php',
            key: 'laravel-swagger-attributes'
        );
    }

    public function boot(): void
    {
        $this->publishes(
            paths: [
                __DIR__ . '/../../config/laravel-swagger-attributes.php' => config_path('laravel-swagger-attributes.php'),
            ],
            groups: 'laravel-swagger-attributes'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                LaravelSwaggerAttributesGenerate::class,
            ]);
        }
    }
}
