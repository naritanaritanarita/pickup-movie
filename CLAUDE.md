# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Laravel 12 + TypeScriptで構築された映画発見アプリケーションです。The Movie Database (TMDB) APIと連携し、カテゴリ別（人気、高評価、上映中）の映画閲覧、言語・配信サービスによるフィルタリング、予告編付きの詳細情報表示が可能です。

## 開発コマンド

### セットアップ
```bash
# PHP依存関係のインストール
composer install

# Node.js依存関係のインストール
npm install

# 環境ファイルのコピーと設定
cp .env.example .env
# .envファイルにTMDB_API_KEYを追加

# アプリケーションキーの生成
php artisan key:generate

# SQLiteデータベースの作成（デフォルト）
touch database/database.sqlite

# マイグレーション実行
php artisan migrate
```

### 開発
```bash
# 開発サーバーの起動（推奨 - すべてのサービスを同時実行）
composer dev
# 実行内容: Laravel server + queue worker + pail logs + Vite dev server

# または個別に実行:
php artisan serve                    # Laravelサーバー（http://localhost:8000）
php artisan queue:listen --tries=1   # キューワーカー
php artisan pail --timeout=0         # リアルタイムログビューアー
npm run dev                          # Vite開発サーバー（アセット用）
```

### ビルド
```bash
# 本番用フロントエンドアセットのビルド
npm run build
```

### テスト
```bash
# PHPUnitテストの実行
composer test
# 以下と同等: php artisan config:clear && php artisan test

# 特定のテストを実行
php artisan test --filter=TestName
```

### キャッシュ
```bash
# 映画キャッシュのウォームアップ（全言語/サービスの人気映画を取得）
php artisan cache:movies

# 各種Laravelキャッシュのクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### コード品質
```bash
# Laravel Pint（コードフォーマッター）の実行
vendor/bin/pint
```

## アーキテクチャ

### バックエンド構造

**コアサービス層**: `app/Services/TMDBService.php`
- すべてのTMDB API連携を集約
- APIレスポンスのキャッシング処理（TTL: 24時間）
- 言語フィルタリング（原語 vs 表示言語）の提供
- TMDB配信プロバイダーによる配信サービスフィルタリング
- 主要メソッド:
  - `getPopularMovies()`, `getTopRatedMovies()`, `getNowPlayingMovies()`
  - `discoverMovies()` - フィルター付き柔軟な検索
  - `getMovieDetails()` - クレジット、動画、類似作品を含む完全な映画詳細
  - `getOfficialTrailer()` - 公式トレーラー/ティーザーを優先して取得

**コントローラー**: `app/Http/Controllers/MovieController.php`
- `discover()` - フィルター付きメイン一覧ページ
- `loadMore()` - 無限スクロール用APIエンドポイント（JSON返却）
- `show()` - 映画詳細ページ

**ルート**: `routes/web.php`
- `/` および `/discover` - 映画発見ページ
- `/load-more` - ページネーションエンドポイント
- `/movies/{id}` - 映画詳細
- `/terms`, `/privacy` - 静的ページ

**キャッシング戦略**:
- Laravelのキャッシュファサードを使用（デフォルトではデータベース設定）
- キャッシュキー: `{category}_{language}_{streaming}_{page}` （例: `popular_ja-JP_all_1`）
- TTL: 24時間（86400秒）
- Artisanコマンド `cache:movies` で1〜3ページ目を事前キャッシュ

**バックグラウンドジョブ**:
- キュー接続: database（`.env`の`QUEUE_CONNECTION`を参照）
- キューワーカーは `php artisan queue:listen --tries=1` で起動
- Dockerによるcronジョブ設定（`cron/Dockerfile`）で定期的に`cache:movies`を実行

### フロントエンド構造

**ビルドシステム**: Vite + Laravel Vite Plugin
- エントリーポイント: `resources/js/app.ts`（TypeScript）と `resources/css/app.css`
- エイリアス設定: `@` → `/resources/js`
- 本番ビルド出力先: `public/build/`
- CDN ベースURL: `https://pickup-movie.onrender.com/build/`（`vite.config.js`で設定）

**TypeScriptコンポーネント**:
- `resources/js/components/movieCarousel.ts` - 横スクロール映画カード
- `resources/js/components/movieList.ts` - 無限スクロール映画グリッド
- 型定義: `resources/js/types/`

**Bladeテンプレート**: `resources/views/movies/`
- `discover.blade.php` - フィルター付きメイン一覧
- `show.blade.php` - 予告編埋め込み付き映画詳細ページ
- スタイリング: Tailwind CSS使用

### 設定

**環境変数**:
- `TMDB_API_KEY` - **必須**: TMDB API v3キー（themoviedb.orgから取得）
- `DB_CONNECTION=sqlite` - デフォルトデータベース（MySQL/PostgreSQLも使用可）
- `QUEUE_CONNECTION=database` - キュードライバー
- `CACHE_STORE=database` - キャッシュドライバー
- `REDIS_*` - Redis設定（オプション、predis/predisパッケージが必要）
- `VITE_ASSET_URL` - アセットCDN用のビルド時変数

**サービス設定**: `config/services.php`
- TMDB APIキーとキャッシュ期間の設定
- キャッシュ期間: short/medium/long（3/6/12時間 - 現在コード内では未使用）

### データフロー

1. **ユーザーリクエスト** → コントローラーがカテゴリ/言語/配信サービスパラメータを検証
2. **TMDBService** → キャッシュをチェック、ミスの場合はTMDB APIを呼び出し
3. **APIレスポンス** → ポスターがある映画のみフィルタリング、20件に制限
4. **キャッシング** → 特定のキーでレスポンスを24時間キャッシュ
5. **ビュー描画** → Bladeテンプレートが映画データ + メタデータを受け取る

**言語処理**:
- `display_language`（例: `ja-JP`） - メタデータのUI言語
- `original_language`（例: `ja`） - 映画の原語でフィルタリング
- 「すべての言語」選択時は display_language のみ使用

**配信サービスフィルタリング**:
- TMDB配信プロバイダーAPIを使用
- `with_watch_providers` と `watch_region=JP` でフィルター適用
- サービスIDは `TMDBService::$streamingServices` で定義

## デプロイ

**Dockerビルド**:
- マルチステージビルド: Node.jsでフロントエンド → PHP + Apacheでバックエンド
- メインDockerfileは80番ポートのApacheで本番イメージをビルド
- `cron/Dockerfile` は定期的なキャッシュウォームアップジョブ用

**本番環境チェックリスト**:
1. `APP_ENV=production` と `APP_DEBUG=false` を設定
2. `TMDB_API_KEY` を設定
3. `npm run build` でアセット生成
4. `composer install --no-dev --optimize-autoloader` を実行
5. `storage/` と `bootstrap/cache/` に適切なファイルパーミッションを設定
6. キューワーカーをデーモンとして設定（supervisor/systemd）
7. `php artisan cache:movies` 用のcronを設定

## 重要な注意事項

- **TMDBレート制限**: `cache:movies` コマンドはリクエスト間に150msの遅延を含む（`usleep(150_000)`）
- **ポスターフィルタリング**: UI一貫性のため `poster_path` がある映画のみ返却
- **SQLite**: デフォルトデータベースは `database/database.sqlite` のSQLite
- **セッション**: データベースに保存（`SESSION_DRIVER=database`）
- **アセットURL**: 本番環境では `vite.config.js` のベースURLをCDNホスティングに使用
- **TypeScript**: 型安全性のため `@types/jquery` を含むTypeScriptプロジェクト
- **Tailwind CSS**: `postcss.config.cjs` でTailwind用のPostCSSを使用
