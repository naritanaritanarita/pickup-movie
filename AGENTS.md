# Repository Guidelines
日本語で簡潔かつ丁寧に回答してください。

## Project Structure & Module Organization
Laravel 本体のコードは `app/` 配下にあり、`Console`、`Http`、`Models` など役割別に分離されています。ブラウザ向けルートは `routes/web.php`、API は `routes/api.php` に定義されるので、機能単位でグループをまとめてください。フロントエンドのスクリプトとスタイルはそれぞれ `resources/js` と `resources/css` に配置し、Blade テンプレートは `resources/views` に揃えます。データベース関連は `database/` 下に集約し、マイグレーション・シーディングはテーブル名と対応する命名に統一します。

## Build, Test, and Development Commands
初回セットアップでは `composer install` と `npm install` を実行します。`composer run dev` は Laravel サーバー、キューリスナー、Pail ログ監視、Vite を一括で起動する開発用プリセットです。フロント側だけ確認したい場合は `npm run dev`、本番ビルドは `npm run build` を使用します。スキーマ変更後は `php artisan migrate --seed` で最新状態を維持し、`.env` を更新した際は `php artisan config:clear` でキャッシュをリセットしてください。

## Coding Style & Naming Conventions
PHP コードは PSR-12 に準拠し、整形は `./vendor/bin/pint` を利用します。コントローラやジョブは `MovieSearchController` のように StudlyCase＋役割名で統一します。Blade テンプレートはファイル名をケバブケースに、`resources/js` の TypeScript モジュールはキャメルケースに揃えてデフォルトエクスポートを抑制します。新規サービスではファサードより依存性注入を優先し、複雑なメソッドには簡潔な PHPDoc を付けてください。

## Testing Guidelines
テストは `tests/Feature` と `tests/Unit` に配置し、ファイル名は `<Subject>Test.php` とします。PR 前には `composer test`（内部で `php artisan test` を実行）でスイート全体を確認してください。DB を伴うテストでは `RefreshDatabase` トレイトを使い、必要最小限のシードだけを読み込んで高速性と独立性を保ちます。カバレッジに不安がある箇所は PR 説明欄で補足しましょう。

## Commit & Pull Request Guidelines
このリポジトリのコミットは「スマホの横並びカード数を４から３つに変更」のように現在形の簡潔な日本語が多く使われています。同様の文体で、変更が混在する場合はコミットを分割してください。Pull Request には課題の背景と解決内容、UI 変更があればスクリーンショットや動画、関連 Issue 番号を添えるとレビューが円滑になります。
