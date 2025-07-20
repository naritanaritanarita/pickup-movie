<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="movie-category" content="{{ $currentCategory }}">
    <meta name="movie-language" content="{{ $currentLanguage }}">
    <meta name="movie-streaming" content="{{ $currentStreaming }}">
    <title>Pickup Movie</title>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="description" content="「フランス語の映画だけ観たい…」そんな声に応える、“言語”で探せる映画検索サービス。NetflixやU-NEXTなど配信サービス別に探せます。">

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
    <div class="mb-3 px-2.5 text-center w-full">
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
    </div>

    <div class="w-full flex justify-center mb-7">
        <div class="w-[800px] max-w-full min-h-[220px]">
            <div class="block w-full">
                <div class="mb-4 w-full text-center">
                    <div class="text-xs text-movie-gray mb-1 text-center">カテゴリー</div>
                    <div class="flex justify-center flex-wrap gap-2.5 mx-auto w-full">
                        <a href="/?category=popular&language={{ $currentLanguage }}&streaming={{ $currentStreaming }}"
                           class="py-2 px-4 bg-movie-panel rounded text-movie-light text-sm transition-colors inline-block
                                     {{ $currentCategory === 'popular' ? 'bg-movie-panel-active' : '' }} hover:bg-movie-panel-hover">
                            人気
                        </a>
                        <a href="/?category=top_rated&language={{ $currentLanguage }}&streaming={{ $currentStreaming }}"
                           class="py-2 px-4 bg-movie-panel rounded text-movie-light text-sm transition-colors inline-block
                                     {{ $currentCategory === 'top_rated' ? 'bg-movie-panel-active' : '' }} hover:bg-movie-panel-hover">
                            高評価
                        </a>
                        <a href="/?category=now_playing&language={{ $currentLanguage }}&streaming={{ $currentStreaming }}"
                           class="py-2 px-4 bg-movie-panel rounded text-movie-light text-sm transition-colors inline-block
                                     {{ $currentCategory === 'now_playing' ? 'bg-movie-panel-active' : '' }} hover:bg-movie-panel-hover">
                            上映中
                        </a>
                    </div>
                </div>

                <div class="mb-4 w-full text-center">
                    <div class="text-xs text-movie-gray mb-1 text-center">言語</div>
                    <div class="flex justify-center flex-wrap gap-2.5 mx-auto w-full">
                        @foreach ($languages as $code => $name)
                            <a href="/?category={{ $currentCategory }}&language={{ $code }}&streaming={{ $currentStreaming }}"
                               class="py-1.5 px-3 bg-movie-panel rounded text-movie-light text-sm transition-colors inline-block
                                     {{ $currentLanguage === $code ? 'bg-movie-panel-active' : '' }} hover:bg-movie-panel-hover">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4 w-full text-center">
                    <div class="text-xs text-movie-gray mb-1 text-center">配信サービス</div>
                    <div class="flex justify-center flex-wrap gap-2.5 mx-auto w-full">
                        @foreach ($streamingServices as $id => $name)
                            <a href="/?category={{ $currentCategory }}&language={{ $currentLanguage }}&streaming={{ $id }}"
                               class="py-1.5 px-3 bg-movie-panel rounded text-movie-light text-sm transition-colors inline-block
                                     {{ (string)$currentStreaming === (string)$id ? 'bg-movie-panel-active' : '' }} hover:bg-movie-panel-hover">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full flex justify-center">
        <div class="w-[1100px] max-w-full min-h-[300px]">
            @if(count($movies) > 0)
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-1.5 sm:gap-3 md:gap-5 w-full" id="movie-grid">
                    @foreach ($movies as $movie)
                        @if($movie['poster_path'])
                            <div class="relative transition-transform duration-200 cursor-pointer hover:scale-105 movie-item" data-id="{{ $movie['id'] }}">
                                <picture>
                                    <source
                                        media="(max-width: 640px)"
                                        srcset="https://image.tmdb.org/t/p/w185{{ $movie['poster_path'] }}">
                                    <img
                                        src="https://image.tmdb.org/t/p/w342{{ $movie['poster_path'] }}"
                                        alt="{{ $movie['title'] }}"
                                        class="w-full aspect-poster object-cover rounded-lg shadow-movie-poster"
                                        loading="eager"
                                    >
                                </picture>
                                <div class="movie-title-overlay">
                                    {{ $movie['title'] }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>


                <div class="text-center p-5 mt-5 hidden" id="loading">
                    <div class="inline-block w-[30px] h-[30px] border-3 border-movie-panel rounded-full border-t-movie-light spin-animation"></div>
                </div>
            @else
                <div class="w-full max-w-[600px] mx-auto text-center py-12 text-movie-gray text-base bg-movie-panel/20 rounded-lg">
                    <p>条件に一致する映画が見つかりませんでした</p>
                </div>
            @endif
        </div>
    </div>

    <div class="w-full text-xs text-movie-muted text-center mt-7">
        映画情報提供元: TMDb<br>
        配信情報提供元: JustWatch
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
