# コーディングスタイル

## 一般的なルール

- インデントは4スペース（PHP）または2スペース（TypeScript/JavaScript）を使用
- 行の長さは120文字以内を推奨
- ファイル末尾に改行を含める
- 末尾の空白を削除する

## PHP/Laravel

- PSR-12コーディング規約に従う
- Laravel Pint を使用してコードフォーマット: `vendor/bin/pint`
- メソッドチェーンは改行してインデント
- 型宣言を可能な限り使用（引数、戻り値）
- 配列は短縮構文 `[]` を使用（`array()` は使わない）

```php
// Good
public function getMovies(string $category): array
{
    return $this->tmdbService
        ->getPopularMovies($category)
        ->filter(fn($movie) => $movie['poster_path']);
}

// Bad
public function getMovies($category)
{
    return $this->tmdbService->getPopularMovies($category)->filter(function($movie) {
        return $movie['poster_path'];
    });
}
```

## TypeScript/JavaScript

- Prettier でフォーマット
- セミコロンを使用
- シングルクォートを使用
- アロー関数を優先
- `const` を優先、必要な場合のみ `let`、`var` は使用しない
- 型は明示的に宣言（`any` は避ける）

```typescript
// Good
const fetchMovies = async (category: string): Promise<Movie[]> => {
    const response = await fetch(`/api/movies?category=${category}`);
    return response.json();
};

// Bad
var fetchMovies = function(category) {
    return fetch(`/api/movies?category=${category}`).then(function(response) {
        return response.json();
    });
};
```

## 命名規則

- **PHP**:
  - クラス名: `PascalCase` (例: `TMDBService`, `MovieController`)
  - メソッド名: `camelCase` (例: `getPopularMovies`)
  - 変数: `camelCase` (例: `$movieId`, `$streamingService`)
  - 定数: `SCREAMING_SNAKE_CASE` (例: `CACHE_TTL`)

- **TypeScript**:
  - クラス/インターフェース: `PascalCase` (例: `MovieCarousel`, `MovieData`)
  - 関数/変数: `camelCase` (例: `loadMoreMovies`, `currentPage`)
  - 定数: `SCREAMING_SNAKE_CASE` (例: `API_BASE_URL`)

- **ファイル名**:
  - PHP: `PascalCase.php` (例: `MovieController.php`)
  - TypeScript: `camelCase.ts` (例: `movieCarousel.ts`)
  - Blade: `kebab-case.blade.php` (例: `movie-detail.blade.php`)

## コメント

- コードで意図が明確でない場合のみコメントを追加
- **なぜ**を説明し、**何を**は説明しない
- PHPDoc/JSDocは公開API、複雑なメソッドに使用

```php
// Good
// TMDBのレート制限を回避するため150ms待機
usleep(150_000);

// Bad
// 150マイクロ秒スリープ
usleep(150_000);
```
