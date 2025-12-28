# テスト規約

---
paths: tests/**/*.php
---

## テスト戦略

- ユニットテスト: サービス層のビジネスロジック
- フィーチャーテスト: HTTPリクエスト/レスポンス、ルーティング
- カバレッジ目標: 主要機能は80%以上

## テストの実行

```bash
# すべてのテストを実行
composer test
# または
php artisan test

# 特定のテストを実行
php artisan test --filter=MovieControllerTest

# カバレッジレポート付き
php artisan test --coverage
```

## テストの構造

### ユニットテスト例（TMDBService）

```php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TMDBService;
use Illuminate\Support\Facades\Http;

class TMDBServiceTest extends TestCase
{
    public function test_filters_movies_with_posters_only()
    {
        $service = new TMDBService();

        $movies = [
            ['id' => 1, 'title' => 'Movie 1', 'poster_path' => '/path1.jpg'],
            ['id' => 2, 'title' => 'Movie 2', 'poster_path' => null],
            ['id' => 3, 'title' => 'Movie 3', 'poster_path' => '/path3.jpg'],
        ];

        $filtered = $service->filterMoviesWithPosters($movies);

        $this->assertCount(2, $filtered);
        $this->assertEquals(1, $filtered[0]['id']);
        $this->assertEquals(3, $filtered[1]['id']);
    }
}
```

### フィーチャーテスト例（MovieController）

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TMDBService;

class MovieControllerTest extends TestCase
{
    public function test_discover_page_loads_successfully()
    {
        $response = $this->get('/discover?category=popular');

        $response->assertStatus(200);
        $response->assertViewIs('movies.discover');
        $response->assertViewHas('movies');
    }

    public function test_rejects_invalid_category()
    {
        $response = $this->get('/discover?category=invalid');

        $response->assertStatus(400);
    }

    public function test_load_more_returns_json()
    {
        $response = $this->get('/load-more?category=popular&page=2');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'movies',
            'hasMore'
        ]);
    }
}
```

## モックとスタブ

- 外部API（TMDB）は必ずモック
- `Http::fake()` を使用してHTTPリクエストをモック
- キャッシュは `Cache::shouldReceive()` でモック

```php
use Illuminate\Support\Facades\Http;

public function test_fetches_popular_movies_from_api()
{
    Http::fake([
        'api.themoviedb.org/*' => Http::response([
            'results' => [
                ['id' => 1, 'title' => 'Test Movie', 'poster_path' => '/test.jpg']
            ]
        ], 200)
    ]);

    $service = new TMDBService();
    $movies = $service->getPopularMovies('ja-JP', 'all');

    $this->assertNotEmpty($movies);
    $this->assertEquals('Test Movie', $movies[0]['title']);
}
```

## テストデータ

- ファクトリーを使用してテストデータ生成
- 実際のAPIレスポンス構造を模倣
- テストごとにデータベースをリセット（`RefreshDatabase` トレイト使用）

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    public function test_example()
    {
        // データベースはテストごとにリセットされる
    }
}
```

## テスト命名規則

- メソッド名は `test_` で始める
- 何をテストしているか明確に記述
- 日本語でも可（`test_人気映画を取得できる`）

```php
// Good
test_filters_movies_with_posters_only()
test_rejects_invalid_category()
test_caches_api_response_for_24_hours()

// Bad
test1()
testMovies()
test_it_works()
```

## アサーション

- 適切なアサーションメソッドを使用
- カスタムメッセージで失敗理由を明確に

```php
// Good
$this->assertCount(20, $movies, 'Should return exactly 20 movies');
$this->assertTrue($response->successful(), 'API request should succeed');

// Bad
$this->assertEquals(true, count($movies) == 20);
```

## テスト環境

- テスト用の `.env.testing` ファイルを使用
- SQLiteインメモリデータベースを推奨（高速）
- キャッシュドライバーは `array` を使用

```
# .env.testing
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
```

## CIで実行

- GitHub Actions や他のCIツールでテストを自動実行
- PHPUnitカバレッジレポートを生成
- テスト失敗時はデプロイを中止
