<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Captcha</title>
    <style>
        body {
            text-align: center;
            margin: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        .captcha-container {
            position: relative;
            display: inline-block;
            direction: ltr;
            user-select: none;
        }
        input[type="image"] {
            display: block;
            border-radius: 6px;
            box-shadow: 0 0 10px #000;
            max-width: 90vw;
            height: auto;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: background 0.3s;
        }
        input[type="image"]:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        .error-box {
            max-width: 600px;
            margin: 0 auto 1.5rem auto;
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .error-box ul {
            margin: 0;
            padding-left: 1.2rem;
            font-size: 0.875rem;
        }
        .success {
            color: #4caf50;
            font-weight: bold;
            margin: 15px 0;
        }

        

.timer-bar {
    width: 300px;
    height: 20px;
    background: #444;
    border-radius: 5px;
    overflow: hidden;
    margin: 20px auto;
    box-shadow: 0 0 5px #000 inset;
    position: relative;
}

.progress {
    width: 100%;
    height: 100%;
    background: linear-gradient(to right, #4caf50, #f44336);
    animation: countdown 30s linear forwards;
}

@keyframes countdown {
    from { width: 100%; }
    to { width: 0%; }
}

.progress-text, .expired-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.8rem;
    font-weight: bold;
    color: #fff;
    pointer-events: none;
    white-space: nowrap;
}

.progress-text {
    animation: hideText 0s linear forwards;
    animation-delay: 30s;
}

@keyframes hideText {
    to { visibility: hidden; }
}

.expired-text {
    visibility: hidden;
    animation: showText 0s linear forwards;
    animation-delay: 30s;
}

@keyframes showText {
    to { visibility: visible; }
}
    </style>
</head>
<body>
    <h2>Show us you are human</h2>
<p>Click the broken circle</p>
    @if ($errors->any())
        <div class="error-box">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('message'))
        <div class="success">{{ session('message') }}</div>
    @endif

    <div class="captcha-container" style="width: {{ $width }}px; height: {{ $height }}px;">
        <form method="POST" action="{{ route('ccaptcha.verify') }}" id="captcha-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}" />
            <input type="image" src="{{ $imageBase64 }}" name="captcha_click" alt="Captcha" id="captcha-image" />
        </form>
    </div>
    
<div class="timer-bar">
    <div class="progress"></div>
    <span class="progress-text">Time left to solve captcha</span>
    <span class="expired-text">captcha expired</span>
</div>

</body>
</html>
