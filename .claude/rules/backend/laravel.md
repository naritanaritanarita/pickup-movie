# Laravel 開発規約

---
paths: app/**/*.php, routes/**/*.php, config/**/*.php, database/**/*.php
---

## アーキテクチャ

- **サービス層を活用**: ビジネスロジックは `app/Services/` に配置
- **コントローラーは薄く**: コントローラーはリクエスト処理とレスポンス返却のみ
- **Eloquent活用**: データベース操作には Eloquent を使用（生SQLは最小限）

## キャッシュ戦略

- Laravel キャッシュファサードを使用
- キャッシュキーは明確で一意にする（カテゴリ、言語、ページなどを含む）
- キャッシュTTLは適切に設定（このプロジェクトでは24時間 = 86400秒）
- キャッシュの強制リフレッシュ機能を実装

```php
// Good - 明確なキャッシュキーとTTL
$cacheKey = "popular_{$language}_{$streaming}_{$page}";
$movies = cache()->remember($cacheKey, 86400, function() use ($params) {
    return $this->fetchFromAPI($params);
});

// Bad - 曖昧なキー、TTL未指定
cache()->remember('movies', function() {
    return $this->fetchMovies();
});
```

## 依存性注入

- コンストラクタインジェクションを使用
- ファサードよりも依存性注入を優先（テスタビリティ向上）

```php
// Good
public function __construct(TMDBService $tmdbService)
{
    $this->tmdbService = $tmdbService;
}

// Bad - ファサード直接使用（テストが困難）
public function getMovies()
{
    return app(TMDBService::class)->getPopularMovies();
}
```

## バリデーション

- リクエストバリデーションはコントローラー内で実装
- 許可された値のリストは定数として定義
- 不正な値は適切なHTTPステータスコードで拒否（400 Bad Request）

```php
// サービスクラスで定数定義
public static array $categories = ['popular', 'top_rated', 'now_playing'];

// コントローラーでバリデーション
if (!in_array($category, TMDBService::$categories)) {
    abort(400, 'Invalid category');
}
```

## エラーハンドリング

- 外部API呼び出しは失敗を想定
- 失敗時は空配列や null を返す（例外を投げるかはケースバイケース）
- ログに記録: `logger()->error()`, `logger()->info()`

```php
$response = Http::get($url, $params);

if (!$response->successful()) {
    logger()->error('TMDB API request failed', [
        'url' => $url,
        'status' => $response->status()
    ]);
    return [];
}
```

## データベース

- デフォルトはSQLite（開発環境）
- マイグレーションファイルは常にバージョン管理
- `SESSION_DRIVER=database` のためセッションテーブルが必要
- `QUEUE_CONNECTION=database` のためジョブテーブルが必要

## Artisan コマンド

- 定期実行タスクは Artisan コマンドとして実装
- `signature` と `description` は必須
- 進捗状況は `$this->info()` で出力

```php
protected $signature = 'cache:movies';
protected $description = 'Cache movies data for all categories and languages';

public function handle(): void
{
    $this->info('Starting cache warming...');
    // 処理
    $this->info('Caching complete!');
}
```

## 環境変数

- 機密情報は `.env` に保存（`TMDB_API_KEY` など）
- `.env.example` にサンプル値を記載
- `config/` ファイル経由でアクセス（直接 `env()` 呼び出しは避ける）

```php
// Good
$apiKey = config('services.tmdb.api_key');

// Bad - config以外で直接env()を使わない
$apiKey = env('TMDB_API_KEY');
```
