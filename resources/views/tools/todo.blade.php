<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TODO管理 - Pickup Movie</title>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="description" content="Pickup MovieのTODO管理ツール。観たい映画や気になる映画をメモして管理できます。">

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
<div class="max-w-[800px] w-full mx-auto relative block">
    <!-- ヘッダー -->
    <div class="mb-6 px-2.5 text-center w-full">
        <a href="/" class="inline-block mb-4">
            <picture>
                @if (app()->environment('development'))
                    <source srcset="{{ asset('images/logo.webp') }}" type="image/webp">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Pickup Movie ロゴ"
                         class="mx-auto w-[180px] sm:w-[220px] h-auto">
                @else
                    <source srcset="{{ secure_asset('images/logo.webp') }}" type="image/webp">
                    <img src="{{ secure_asset('images/logo.png') }}"
                         alt="Pickup Movie ロゴ"
                         class="mx-auto w-[180px] sm:w-[220px] h-auto">
                @endif
            </picture>
        </a>
        <h1 class="text-2xl font-bold text-movie-light mb-2">TODO管理</h1>
        <p class="text-sm text-movie-gray">観たい映画や気になることをメモしておきましょう</p>
    </div>

    <!-- ホームに戻るリンク -->
    <div class="mb-6 text-center">
        <a href="/" class="inline-flex items-center gap-2 py-2 px-4 bg-movie-panel rounded text-movie-light text-sm transition-colors hover:bg-movie-panel-hover">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            ホームに戻る
        </a>
    </div>

    <!-- TODO App Container -->
    <div id="todo-app" class="w-full">
        <!-- 入力フォーム -->
        <div class="mb-6 bg-movie-panel rounded-lg p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row gap-3">
                <input
                    type="text"
                    id="todo-input"
                    placeholder="TODOを入力してください..."
                    class="flex-1 px-4 py-3 bg-movie-dark text-movie-light rounded border-2 border-movie-light/20 focus:border-movie-panel-active focus:outline-none transition-colors"
                />
                <button
                    id="add-todo-btn"
                    class="px-6 py-3 bg-movie-panel-active text-movie-light rounded font-medium hover:bg-opacity-80 transition-all hover:scale-105 whitespace-nowrap"
                >
                    追加
                </button>
            </div>
        </div>

        <!-- フィルターボタン -->
        <div class="mb-4 flex justify-center gap-2 flex-wrap">
            <button
                data-filter="all"
                class="py-2 px-4 bg-movie-panel-active rounded text-movie-light text-sm transition-colors hover:bg-movie-panel-hover"
            >
                すべて
            </button>
            <button
                data-filter="active"
                class="py-2 px-4 bg-movie-panel rounded text-movie-light text-sm transition-colors hover:bg-movie-panel-hover"
            >
                未完了
            </button>
            <button
                data-filter="completed"
                class="py-2 px-4 bg-movie-panel rounded text-movie-light text-sm transition-colors hover:bg-movie-panel-hover"
            >
                完了済み
            </button>
        </div>

        <!-- TODOリスト -->
        <div id="todo-list" class="space-y-3">
            <!-- TODOアイテムはJavaScriptで動的に生成されます -->
            <div class="text-center py-8 text-movie-gray">
                TODOを追加してください
            </div>
        </div>
    </div>

    <!-- 注意書き -->
    <div class="mt-8 text-center text-xs text-movie-muted">
        <p>TODOはブラウザのローカルストレージに保存されます</p>
        <p class="mt-1">ブラウザのデータを削除すると、TODOも削除されますのでご注意ください</p>
    </div>
</div>

<footer class="w-full text-center text-sm text-movie-muted mt-10 pt-10 border-t border-movie-panel">
    <div class="flex justify-center gap-5">
        <a href="/terms" class="hover:underline">利用規約</a>
        <a href="/privacy" class="hover:underline">プライバシーポリシー</a>
    </div>
    <div class="mt-3">&copy; {{ date('Y') }} Pickup Movie</div>
</footer>
</body>
</html>
