<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ __('404') }} · Not Found | {{ config('app.name', 'Civitas') }}</title>
    @vite(['resources/js/mascot-404.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            background: #000;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: #fff;
            overflow: hidden;
        }

        .mascot-bg {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            overflow: hidden;
            background: #000;
        }

        .mascot-bg video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            transition: opacity 0.3s ease;
        }

        .mascot-bg video[data-mascot-side="right"] {
            opacity: 1;
        }

        .mascot-bg video[data-mascot-side="left"] {
            opacity: 0;
        }

        @media (max-width: 767px) {
            .mascot-bg video {
                object-position: 60% center;
            }
        }

        .mascot-scrim {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background: linear-gradient(
                180deg,
                rgba(0, 0, 0, 0.55) 0%,
                rgba(0, 0, 0, 0.2) 45%,
                rgba(0, 0, 0, 0.65) 100%
            );
        }

        .mascot-content {
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1.5rem;
        }

        .mascot-content .code {
            font-size: clamp(4rem, 18vw, 10rem);
            font-weight: 800;
            letter-spacing: 0.05em;
            text-shadow: 0 4px 24px rgba(0, 0, 0, 0.7);
        }

        .mascot-content .message {
            font-size: clamp(1rem, 3vw, 1.4rem);
            margin-top: 0.75rem;
            opacity: 0.92;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.7);
        }

        .mascot-content .back {
            display: inline-block;
            margin-top: 1.75rem;
            padding: 0.75rem 1.75rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            backdrop-filter: blur(6px);
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .mascot-content .back:hover {
            background: rgba(255, 255, 255, 0.24);
            border-color: rgba(255, 255, 255, 0.6);
        }

        @media (prefers-reduced-motion: reduce) {
            .mascot-bg video {
                transition: none;
            }
        }
    </style>
</head>
<body>
    <div class="mascot-bg" data-mascot-404 aria-hidden="true">
        <video data-mascot-side="right"
               muted
               playsinline
               preload="auto"
               src="https://cdn.pika.art/results/pika2p5_final/3aa60e9684d74707827dd35a69518aff.mp4"></video>
        <video data-mascot-side="left"
               muted
               playsinline
               preload="auto"
               src="https://cdn.pika.art/results/pika2p5_final/6332a8f9c0344ce3a8c8d7873a744a72.mp4"></video>
        <div class="mascot-scrim"></div>
    </div>

    <main class="mascot-content">
        <div class="code" aria-hidden="true">404</div>
        <p class="message">{{ __('الصفحة غير موجودة') }}.</p>
        <a class="back" href="{{ url('/') }}">{{ __('العودة للرئيسية') }}</a>
    </main>
</body>
</html>