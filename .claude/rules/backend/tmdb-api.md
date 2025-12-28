# TMDB API 連携ルール

---
paths: app/Services/TMDBService.php, app/Http/Controllers/MovieController.php
---

## API呼び出し

- すべてのTMDB API呼び出しは `TMDBService` に集約
- `Http::get()` を使用してリクエスト
- レスポンスは必ず成功チェック: `$response->successful()`
- 失敗時は空配列を返すかログに記録

## レート制限対策

- バッチ処理では各リクエスト間に遅延を挿入
- 現在の設定: 150ms（`usleep(150_000)`）
- 連続的な大量リクエストは避ける

```php
// キャッシュウォームアップ時
foreach ($categories as $category) {
    $this->fetchMovies($category);
    usleep(150_000); // レート制限対策
}
```

## キャッシング必須

- すべてのAPI呼び出しはキャッシュする（TTL: 24時間）
- キャッシュキーには全パラメータを含める
- キャッシュヒット/ミスをログに記録

```php
$cacheKey = "{$category}_{$language}_{$streaming}_{$page}";

if (cache()->has($cacheKey)) {
    logger()->info("Cache HIT for key: $cacheKey");
    return cache()->get($cacheKey);
}

$result = $this->makeApiRequest($endpoint, $params);
cache()->put($cacheKey, $result, 86400);
```

## パラメータ

### 必須パラメータ
- `api_key`: すべてのリクエストに含める
- `language`: 表示言語（例: `ja-JP`）
- `include_adult`: 常に `false` に設定

### オプションパラメータ
- `with_original_language`: 原語フィルター（例: `ja`）
- `with_watch_providers`: 配信サービスID（例: `337` = Disney+）
- `watch_region`: 配信地域（例: `JP`）
- `sort_by`: ソート順（例: `popularity.desc`）

## データフィルタリング

- **ポスター必須**: `poster_path` がある映画のみ返す
- **件数制限**: 20件に制限してUIの一貫性を保つ
- **アダルトコンテンツ除外**: `include_adult=false` 必須

```php
private function filterMoviesWithPosters(array $results, int $limit = 20): array
{
    $filteredResults = array_filter($results, function($movie) {
        return !empty($movie['poster_path']);
    });

    return array_slice(array_values($filteredResults), 0, $limit);
}
```

## 言語処理

- **表示言語** (`display_language`): メタデータの表示言語（例: `ja-JP`）
- **原語** (`original_language`): 映画の制作言語でフィルター（例: `ja`）
- 「すべての言語」選択時は原語フィルターなし

```php
// 言語コードを分割
$parts = explode('-', 'ja-JP'); // ['ja', 'JP']
$langCode = $parts[0];          // 'ja'

return [
    'display_language' => 'ja-JP',     // API表示用
    'original_language' => 'ja'        // フィルター用
];
```

## エンドポイント選択

- カテゴリAPIエンドポイント (`/movie/popular`, `/movie/top_rated`, `/movie/now_playing`) を優先
- 配信サービスや原語フィルターが必要な場合のみ `/discover/movie` を使用
- 詳細情報取得は `/movie/{id}` に `append_to_response` パラメータを使用

```php
// フィルターなし → カテゴリエンドポイント
GET /movie/popular?language=ja-JP

// フィルターあり → discoverエンドポイント
GET /discover/movie?with_watch_providers=337&watch_region=JP&with_original_language=ja
```

## トレーラー取得

- 公式トレーラーを優先（`official: true`）
- タイプ優先順位: `Trailer` > `Teaser`
- サイトは YouTube のみ使用
- 日本語トレーラーがない場合は英語で代替

## 配信サービス

- サービスIDは `TMDBService::$streamingServices` で管理
- 日本の主要サービスを定義（U-NEXT: 84, Disney+: 337, Netflix: 8, など）
- `watch_region=JP` を必ず指定
