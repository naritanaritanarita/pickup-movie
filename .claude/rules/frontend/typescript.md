# TypeScript/フロントエンド規約

---
paths: resources/js/**/*.{ts,tsx}, resources/views/**/*.blade.php
---

## TypeScript

- すべてのJavaScriptは TypeScript で記述
- `any` 型は避け、適切な型定義を使用
- 型定義ファイルは `resources/js/types/` に配置
- 外部ライブラリの型定義パッケージをインストール（例: `@types/jquery`）

```typescript
// Good
interface Movie {
    id: number;
    title: string;
    poster_path: string;
    vote_average: number;
}

const fetchMovie = async (id: number): Promise<Movie> => {
    const response = await fetch(`/movies/${id}`);
    return response.json();
};

// Bad
const fetchMovie = async (id) => {
    const response = await fetch(`/movies/${id}`);
    return response.json();
};
```

## コンポーネント設計

- 各コンポーネントは単一責任
- 再利用可能な機能はユーティリティ関数として分離
- DOM操作は最小限に、可能な限り宣言的に

### 主要コンポーネント

- `movieCarousel.ts`: 横スクロールカルーセル機能
- `movieList.ts`: 無限スクロールグリッド機能

## 無限スクロール実装

- Intersection Observer API を使用
- スクロール終端の検出にセンチネル要素を配置
- ローディング中の重複リクエストを防ぐ

```typescript
const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !isLoading) {
        loadMoreMovies();
    }
});
```

## APIリクエスト

- `fetch` API を使用
- エラーハンドリングを必ず実装
- ローディング状態を管理

```typescript
const loadMore = async () => {
    if (isLoading) return;

    isLoading = true;
    try {
        const response = await fetch(`/load-more?page=${page}`);
        if (!response.ok) throw new Error('Failed to fetch');

        const data = await response.json();
        appendMovies(data.movies);
        hasMore = data.hasMore;
    } catch (error) {
        console.error('Error loading movies:', error);
    } finally {
        isLoading = false;
    }
};
```

## Bladeテンプレート

- レイアウトは一貫性を保つ
- Tailwind CSS クラスを使用してスタイリング
- JavaScriptのデータ受け渡しには `@json()` ディレクティブを使用
- XSS対策: ユーザー入力は常にエスケープ（`{{ }}` 使用）

```blade
{{-- Good --}}
<div class="movie-grid" data-movies="@json($movies)">
    @foreach($movies as $movie)
        <div class="movie-card">
            <h3>{{ $movie['title'] }}</h3>
        </div>
    @endforeach
</div>

{{-- Bad - XSS脆弱性 --}}
<h3>{!! $movie['title'] !!}</h3>
```

## Vite 統合

- エントリーポイント: `resources/js/app.ts`, `resources/css/app.css`
- エイリアス `@` を使用してインポート: `import { foo } from '@/utils/bar'`
- 開発時は HMR（Hot Module Replacement）を活用
- 本番ビルド前に必ず `npm run build` を実行

## パフォーマンス

- 画像は遅延読み込み（`loading="lazy"`）を使用
- 大量のDOM要素を追加する場合は DocumentFragment を使用
- イベントリスナーは適切にクリーンアップ

```typescript
// Good - DocumentFragment使用
const fragment = document.createDocumentFragment();
movies.forEach(movie => {
    const card = createMovieCard(movie);
    fragment.appendChild(card);
});
container.appendChild(fragment);

// Bad - 個別に追加（パフォーマンス低下）
movies.forEach(movie => {
    const card = createMovieCard(movie);
    container.appendChild(card);
});
```

## アクセシビリティ

- 画像には必ず `alt` 属性を設定
- インタラクティブ要素にはキーボードアクセスを確保
- セマンティックなHTML要素を使用

```html
<!-- Good -->
<button class="load-more-btn" aria-label="さらに映画を読み込む">
    もっと見る
</button>

<!-- Bad -->
<div class="load-more-btn" onclick="loadMore()">
    もっと見る
</div>
```
