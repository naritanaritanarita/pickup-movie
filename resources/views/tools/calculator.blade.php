<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>電卓 - Pickup Movie</title>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="description" content="シンプルな電卓機能。四則演算（足し算、引き算、掛け算、割り算）に対応。">

    @if (app()->environment('development'))
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
        <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}">
    @else
        @php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        @endphp
        <link rel="stylesheet" href="{{ secure_asset('build/' . $manifest['resources/css/app.css']['file']) }}">
        <script type="module" src="{{ secure_asset('build/' . $manifest['resources/js/app.ts']['file']) }}"></script>
        <link rel="apple-touch-icon" sizes="180x180" href="{{ secure_asset('favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ secure_asset('favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ secure_asset('favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ secure_asset('favicon/site.webmanifest') }}">
        <link rel="shortcut icon" href="{{ secure_asset('favicon/favicon.ico') }}">
    @endif
</head>
<body class="bg-movie-dark text-movie-light font-sans px-3 sm:px-5 pt-3 pb-5 w-full overflow-x-hidden">
<div class="max-w-[1200px] w-full mx-auto relative block">
    <div class="mb-6 px-2.5 text-center w-full">
        <a href="/" class="inline-block">
            <picture>
                @if (app()->environment('development'))
                    <source srcset="{{ asset('images/logo.webp') }}" type="image/webp">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Pickup Movie ロゴ"
                         class="mx-auto w-[180px] sm:w-[220px] md:w-[260px] h-auto">
                @else
                    <source srcset="{{ secure_asset('images/logo.webp') }}" type="image/webp">
                    <img src="{{ secure_asset('images/logo.png') }}"
                         alt="Pickup Movie ロゴ"
                         class="mx-auto w-[180px] sm:w-[220px] md:w-[260px] h-auto">
                @endif
            </picture>
        </a>
    </div>

    <div class="w-full flex justify-center mb-5">
        <a href="/" class="text-sm text-movie-light hover:text-white transition-colors">
            ← ホームに戻る
        </a>
    </div>

    <div class="w-full flex justify-center">
        <div class="w-full max-w-[400px]">
            <h1 class="text-2xl font-bold text-center mb-6">電卓</h1>

            <div id="calculator" class="bg-movie-panel rounded-lg p-5 shadow-lg">
                <!-- ディスプレイ -->
                <div class="mb-4 bg-movie-dark rounded p-4 text-right">
                    <div class="text-sm text-movie-gray mb-1 min-h-[20px]" id="calc-expression"></div>
                    <div class="text-3xl font-mono" id="calc-display">0</div>
                </div>

                <!-- ボタングリッド -->
                <div class="grid grid-cols-4 gap-2">
                    <!-- 1行目: C, /, *, - -->
                    <button class="calc-btn calc-btn-function col-span-2" data-action="clear">C</button>
                    <button class="calc-btn calc-btn-operator" data-operator="/">÷</button>
                    <button class="calc-btn calc-btn-operator" data-operator="*">×</button>

                    <!-- 2行目: 7, 8, 9, + -->
                    <button class="calc-btn calc-btn-number" data-number="7">7</button>
                    <button class="calc-btn calc-btn-number" data-number="8">8</button>
                    <button class="calc-btn calc-btn-number" data-number="9">9</button>
                    <button class="calc-btn calc-btn-operator row-span-2" data-operator="+">+</button>

                    <!-- 3行目: 4, 5, 6 -->
                    <button class="calc-btn calc-btn-number" data-number="4">4</button>
                    <button class="calc-btn calc-btn-number" data-number="5">5</button>
                    <button class="calc-btn calc-btn-number" data-number="6">6</button>

                    <!-- 4行目: 1, 2, 3, - -->
                    <button class="calc-btn calc-btn-number" data-number="1">1</button>
                    <button class="calc-btn calc-btn-number" data-number="2">2</button>
                    <button class="calc-btn calc-btn-number" data-number="3">3</button>
                    <button class="calc-btn calc-btn-operator row-span-2" data-operator="-">−</button>

                    <!-- 5行目: 0, ., = -->
                    <button class="calc-btn calc-btn-number col-span-2" data-number="0">0</button>
                    <button class="calc-btn calc-btn-function" data-action="decimal">.</button>

                    <!-- =ボタン -->
                    <button class="calc-btn calc-btn-equals col-span-3" data-action="equals">=</button>
                </div>
            </div>

            <div class="mt-5 text-center text-sm text-movie-gray">
                <p>基本的な四則演算に対応しています</p>
            </div>
        </div>
    </div>
</div>

<footer class="w-full text-center text-sm text-movie-muted mt-10 pt-10 border-t border-movie-panel">
    <div class="flex justify-center gap-5">
        <a href="/terms" class="hover:underline">利用規約</a>
        <a href="/privacy" class="hover:underline">プライバシーポリシー</a>
    </div>
    <div class="mt-3">&copy; {{ date('Y') }} Pickup Movie</div>
</footer>

<style>
    .calc-btn {
        @apply py-4 px-4 rounded text-lg font-semibold transition-all duration-150;
        @apply bg-movie-dark text-movie-light;
        @apply hover:bg-movie-light hover:text-movie-dark;
        @apply active:scale-95;
    }

    .calc-btn-number {
        @apply bg-movie-dark;
    }

    .calc-btn-operator {
        @apply bg-blue-600 text-white;
        @apply hover:bg-blue-500;
    }

    .calc-btn-function {
        @apply bg-gray-600 text-white;
        @apply hover:bg-gray-500;
    }

    .calc-btn-equals {
        @apply bg-green-600 text-white;
        @apply hover:bg-green-500;
    }
</style>
</body>
</html>
