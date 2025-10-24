<?php

namespace Forutan\CCaptcha\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Forutan\CCaptcha\Helpers\CircleCaptchaImage;
use Illuminate\Routing\Controller;
use Carbon\Carbon;

class CCaptchaController extends Controller
{
    protected string $context;

    public function __construct()
    {
        $this->context = session('ccaptcha_context','default');
        if (!$this->context) abort(400);
    }

    public function show()
    {
        $context = $this->context;

        if (session("ccaptcha_passed.$context", false)) {
            return redirect(config("ccaptcha.redirect_on_pass.$context", '/'));
        }

        $width = config('ccaptcha.width', 500);
        $height = config('ccaptcha.height', 400);
        $circleCount = rand(config('ccaptcha.min_circle_count', 10), config('ccaptcha.max_circle_count', 15));
        $rMin = config('ccaptcha.radius_min', 20);
        $rMax = config('ccaptcha.radius_max', 35);

        $result = CircleCaptchaImage::generate($width,$height,$circleCount,$rMin,$rMax);

        $token = Str::random(40);
        session([
            "ccaptcha_answer.$context" => array_merge($result['brokenCircle'], ['created_at'=>now(),'token'=>$token])
        ]);

        return view('ccaptcha::ccaptcha', [
            'imageBase64' => $result['imageBase64'],
            'token' => $token,
            'width' => $width,
            'height' => $height,
        ]);
    }

    public function verify(Request $request)
    {
        $context = $this->context;
        $captchaAnswer = session("ccaptcha_answer.$context");

        if (!$captchaAnswer) {
            // $request->session()->regenerateToken();
            return redirect()->route('ccaptcha.show')
                ->withErrors(['error' => 'Captcha expired or not found.']);
        }

        $blockKey = "ccaptcha_blocked.$context";
        if (session()->has($blockKey)) {
            $blockedUntil = session($blockKey);

            if (now()->timestamp < $blockedUntil) {
                $remaining = $blockedUntil - now()->timestamp;
                $humanRemaining = now()->addSeconds($remaining)->diffForHumans(null, true);

                return redirect()->route('ccaptcha.show')->withErrors([
                    'error' => "Too many failed attempts. Try again in $humanRemaining."
                ]);
            }

            session()->forget($blockKey);
            session()->forget("ccaptcha_attempts.$context");
        }

        $token = $request->input('token');
        $clickX = $request->input('captcha_click_x');
        $clickY = $request->input('captcha_click_y');

        if (!$token || !is_numeric($clickX) || !is_numeric($clickY) || $token !== $captchaAnswer['token']) {
            session()->increment("ccaptcha_attempts.$context");
            return redirect()->route('ccaptcha.show')->withErrors(['error' => 'Invalid captcha attempt.']);
        }

        $expireSeconds = config('ccaptcha.expire_seconds', 30);
        if (now()->diffInSeconds($captchaAnswer['created_at'], true) > $expireSeconds) {
            session()->forget("ccaptcha_answer.$context");
            session()->increment("ccaptcha_attempts.$context");
            return redirect()->route('ccaptcha.show')->withErrors(['error' => 'Captcha timed out.']);
        }

        $distance = sqrt(pow($clickX - $captchaAnswer['x'], 2) + pow($clickY - $captchaAnswer['y'], 2));
        $maxAttempts = config('ccaptcha.max_attempts', 5);
        $attempts = session()->increment("ccaptcha_attempts.$context");

        if ($attempts >= $maxAttempts) {
            $blockDuration = config('ccaptcha.block_duration_minutes', 60);
            session(["ccaptcha_blocked.$context" => now()->addMinutes($blockDuration)->timestamp]);

            return redirect()->route('ccaptcha.show')->withErrors([
                'error' => "Too many failed attempts. Blocked for $blockDuration minutes."
            ]);
        }

        if ($distance <= $captchaAnswer['r']) {
            session()->regenerate();
            session(["ccaptcha_passed.$context" => true]);
            session()->forget([
                "ccaptcha_answer.$context",
                "ccaptcha_attempts.$context",
                "ccaptcha_blocked.$context"
            ]);

            return redirect()->intended(config("ccaptcha.redirect_on_pass.$context", '/'))
                ->with('message', 'Circle Captcha successfully verified!');
        }

        return redirect()->route('ccaptcha.show')->withErrors([
            'error' => "Wrong selection. Attempt $attempts of $maxAttempts."
        ]);
    }
}
