# セキュリティ規約

## 一般原則

- 決して機密情報をコードにハードコーディングしない
- すべてのユーザー入力を検証・サニタイズ
- HTTPS通信を強制（本番環境）
- セキュリティアップデートを定期的に適用

## 環境変数と機密情報

- APIキー、パスワードは `.env` に保存
- `.env` ファイルは **絶対に** バージョン管理に含めない（`.gitignore` に追加済み）
- `.env.example` にはサンプル値のみ記載

```bash
# Good - .env
TMDB_API_KEY=actual_secret_key_here

# Good - .env.example
TMDB_API_KEY=your_tmdb_api_key_here
```

## XSS（クロスサイトスクリプティング）対策

- Bladeテンプレートでは `{{ }}` を使用（自動エスケープ）
- `{!! !!}` は信頼できるHTMLのみに使用
- ユーザー入力を直接JavaScriptに埋め込まない

```blade
{{-- Good - 自動エスケープ --}}
<h1>{{ $movie['title'] }}</h1>

{{-- Dangerous - XSS脆弱性 --}}
<h1>{!! $userInput !!}</h1>
<script>var title = "{{ $userInput }}";</script>
```

## CSRF対策

- すべてのPOST/PUT/DELETE リクエストに CSRF トークンを含める
- Laravelの `@csrf` ディレクティブを使用

```blade
<form method="POST" action="/movies">
    @csrf
    <!-- form fields -->
</form>
```

## SQLインジェクション対策

- Eloquent ORM またはクエリビルダーを使用
- 生SQLは避ける、必要な場合はパラメータバインディングを使用

```php
// Good - Eloquentまたはクエリビルダー
$movies = DB::table('movies')->where('id', $id)->get();

// Dangerous - 生SQLでの直接補間
$movies = DB::select("SELECT * FROM movies WHERE id = $id");

// Good - パラメータバインディング
$movies = DB::select("SELECT * FROM movies WHERE id = ?", [$id]);
```

## 認証と認可

- 機密操作には認証を必須に
- ミドルウェアで認証・認可をチェック
- 現在このプロジェクトは公開読み取り専用だが、管理機能追加時は要検討

```php
// 将来の管理機能用
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/admin/movies', [AdminController::class, 'store']);
});
```

## APIキーの保護

- TMDB APIキーは `.env` に保存
- クライアントサイドに露出させない
- サーバーサイドでのみ使用

```php
// Good - サーバーサイドでAPIキーを使用
$apiKey = config('services.tmdb.api_key');
$response = Http::get($url, ['api_key' => $apiKey]);

// Bad - クライアントに露出
<script>
const apiKey = "{{ config('services.tmdb.api_key') }}";
</script>
```

## バリデーション

- すべての外部入力を検証
- ホワイトリスト方式を使用（許可されたもののみ受け入れ）
- 不正な値は適切なHTTPステータスコードで拒否

```php
// Good - ホワイトリスト検証
$allowedCategories = ['popular', 'top_rated', 'now_playing'];
if (!in_array($category, $allowedCategories)) {
    abort(400, 'Invalid category');
}

// Bad - ブラックリスト方式
if ($category === 'admin' || $category === 'secret') {
    abort(400);
}
```

## ファイルアップロード

- 現在このプロジェクトではファイルアップロード機能なし
- 将来追加する場合:
  - ファイルタイプを検証
  - ファイルサイズを制限
  - ファイル名をサニタイズ
  - 公開ディレクトリ外に保存

## セッションセキュリティ

- セッションCookieに `HttpOnly`, `Secure`, `SameSite` フラグを設定
- `config/session.php` で設定済み

```php
// config/session.php
'http_only' => true,
'secure' => env('SESSION_SECURE_COOKIE', true),
'same_site' => 'lax',
```

## レート制限

- API エンドポイントにレート制限を適用
- Laravel のレートリミッターを使用

```php
// 将来のAPI保護用
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/api/movies', [MovieController::class, 'index']);
});
```

## 依存関係の管理

- 定期的に `composer update` と `npm update` を実行
- セキュリティアドバイザリをチェック
- 古いパッケージバージョンは更新

```bash
# 脆弱性チェック
composer audit
npm audit
```

## エラーメッセージ

- 本番環境では詳細なエラーメッセージを表示しない
- `APP_DEBUG=false` を設定
- エラーはログに記録、ユーザーには一般的なメッセージを表示

```php
// Good - 本番環境
APP_DEBUG=false
LOG_LEVEL=error

// Bad - 本番環境での設定
APP_DEBUG=true  // スタックトレースが露出
```

## HTTPS強制

- 本番環境では HTTPS を強制
- `TrustProxies` ミドルウェアを適切に設定

```php
// app/Http/Middleware/TrustProxies.php
protected $proxies = '*';
```

## セキュリティヘッダー

- CSP（Content Security Policy）を設定
- X-Frame-Options, X-Content-Type-Options などを設定

```php
// ミドルウェアでセキュリティヘッダーを追加
return $next($request)
    ->header('X-Frame-Options', 'SAMEORIGIN')
    ->header('X-Content-Type-Options', 'nosniff')
    ->header('Referrer-Policy', 'no-referrer-when-downgrade');
```
