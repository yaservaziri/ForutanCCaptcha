<?php

namespace Forutan\CCaptcha\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Forutan\CCaptcha\Http\Middleware\EnsureCCaptchaIsVerified;
use Forutan\CCaptcha\Http\Middleware\RedirectIfCCaptchaAlreadyPassed;
use Forutan\CCaptcha\Commands\CCaptchaPrepareImages;

class CCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'ccaptcha');
        $this->publishes([
            __DIR__ . '/../Config/ccaptcha.php' => config_path('ccaptcha.php'),
        ], 'ccaptcha-config');


        if ($this->app->runningInConsole()) {
            $this->commands([
                CCaptchaPrepareImages::class,
            ]);
        }

        $this->app->make(Router::class)->aliasMiddleware('ccaptcha.verified', EnsureCCaptchaIsVerified::class);
        $this->app->make(Router::class)->aliasMiddleware('ccaptcha.redirect_if_passed', RedirectIfCCaptchaAlreadyPassed::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/ccaptcha.php',
            'ccaptcha'
        );
    }
}
