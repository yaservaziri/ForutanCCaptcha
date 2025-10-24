<?php

namespace Forutan\CCaptcha\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfCCaptchaAlreadyPassed
{
    public function handle(Request $request, Closure $next, $context = 'default')
    {
        $context = $context ?: session('ccaptcha_context', 'default');       
        if (session("ccaptcha_passed.$context", false)) {
            return redirect(config("ccaptcha.redirect_on_pass.$context", '/'));
        }
        return $next($request);
    }
}
