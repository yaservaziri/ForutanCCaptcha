<?php

namespace Forutan\CCaptcha\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCCaptchaIsVerified
{
    public function handle(Request $request, Closure $next, $context = 'default')
    {
        if (!session("ccaptcha_passed.$context", false)) {
            session(['ccaptcha_context' => $context,]);
            return redirect()->route('ccaptcha.show')
                ->with('error', 'Please verify you are human to continue.');
        }
        return $next($request);
    }
}
