# Console Commands Documentation

## DeactivateInactiveUsers

### 概要

アクティブだが投稿がないユーザーを非アクティブ化するコマンド。

### コマンド

```php
php artisan users:deactivate-inactive
```

### 説明

-   投稿がないアクティブユーザーを検索し、`active`フラグを`false`に更新します。
-   更新されたユーザー数を出力します。

---

## TruncateAllTables

### 概要

すべてのテーブルのデータを削除（truncate）するコマンド。

### コマンド

```php
php artisan db:truncate-all {--force}
```

### 説明

-   外部キー制約を無効化してから、すべてのテーブルをトランケートします。
-   `--force`オプションを指定しない場合、確認プロンプトが表示されます。
-   `migrations`テーブルは除外されます。

---

## UserInsertBenchmark

### 概要

`users`テーブルへのデータ挿入方法をベンチマークするコマンド。

### コマンド

```php
php artisan benchmark:user-insert
```

### 説明

-   以下の方法でデータを挿入し、それぞれの処理時間とメモリ使用量を計測します。
    1. 通常の`insert`（1 件ずつ）
    2. チャンクで`insert`（100 件、1000 件ごと）
    3. バルクインサート（大量データを一括挿入）

---

## UserCreateBenchmark

### 概要

Eloquent の`create`メソッドを使用したデータ挿入方法をベンチマークするコマンド。

### コマンド

```php
php artisan benchmark:user-create
```

### 説明

-   以下の方法でデータを挿入し、それぞれの処理時間とメモリ使用量を計測します。
    1. 通常の`create`（1 件ずつ）
    2. チャンクで`create`（100 件、1000 件ごと）
    3. Factory を使用して一括挿入

---

## GenerateUserAndPostData

### 概要

Factory を使用してユーザーと投稿データを生成するコマンド。

### コマンド

```php
php artisan factory:generate-users-posts {--users=10} {--posts=3}
```

### 説明

-   指定された数のユーザーと、それぞれに紐づく投稿データを生成します。
-   オプション:
    -   `--users`: 作成するユーザー数（デフォルト: 10）
    -   `--posts`: 各ユーザーに紐づける投稿数（デフォルト: 3）

---

## TransactionTestCommand

### 概要

トランザクションの動作をテストするコマンド。

### コマンド

```php
php artisan transaction:test
```

### 説明

-   トランザクション内でユーザーと投稿を作成します。
-   例外が発生した場合、トランザクションがロールバックされます。
-   正常終了時はコミットされます。

---
