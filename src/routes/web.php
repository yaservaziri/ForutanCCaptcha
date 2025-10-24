<?php

use Illuminate\Support\Facades\Route;
use Forutan\CCaptcha\Http\Controllers\CCaptchaController;

Route::group([
    'prefix' => config('ccaptcha.route_prefix', 'ccaptcha'),
    'middleware' => config('ccaptcha.middleware', ['web']),
    'as' => 'ccaptcha.',
], function () {
    Route::get('/', [CCaptchaController::class, 'show'])->name('show')->middleware(['ccaptcha.redirect_if_passed', 'throttle:' . config('ccaptcha.throttle.show', '60,1')]);
    Route::post('/verify', [CCaptchaController::class, 'verify'])->name('verify')->middleware('throttle:' . config('ccaptcha.throttle.verify', '20,1'));;
});
