# データベースのバージョン管理

## 🎯 目的

-   DB スキーマを **再現性ある形で管理**すること
-   コードと同じように、DB も **「ある時点の状態」へ戻せる・進められる**状態を実現する

## 理論的背景

-   **インフラやアプリと同様に、DB もバージョン管理対象**
-   DevOps / GitOps では「すべてをコード化（Infrastructure as Code）」が原則
-   DB も「Schema as Code」として扱う必要がある

## 実現方法

1. **宣言的アプローチ**

    - DB の完全スキーマ（例: `schema.sql`）を常に管理し、環境ごとに差分適用する
    - ツール例: Skeema, Atlas

2. **命令的アプローチ**

    - 「このテーブルを追加」「このカラムを変更」といったマイグレーションスクリプトを積み上げる
    - Laravel Migrations, Flyway, Liquibase など

3. **CI/CD との統合**
    - コードのコミットと同時に DB バージョンも進める
    - 理想は「git のコミット ID = DB スキーマの状態」

# スキーマの変更履歴を管理

## 🎯 目的

-   「どの時点で」「誰が」「どのスキーマ変更を適用したか」を追跡可能にする
-   監査性・再現性・障害対応に必須

## 理論的背景

-   **変更管理 (Change Management)** の一部
-   ITIL や ISO などでも「システム変更の履歴管理」は必須項目
-   データベースも同じく「ログによる可観測性」が求められる

## 実現方法

1. **履歴テーブル方式**

    - 例: Laravel の `migrations` テーブル
    - 適用済みのマイグレーションを記録

2. **DDL ログ収集**

    - MySQL の general log / audit log を使って ALTER/CREATE を記録

3. **ダンプ差分方式**

    - 定期的に `mysqldump --no-data` を取得し、git 管理
    - 「どの時点で schema がどう変わったか」をファイル差分で確認

4. **監査用ツール統合**
    - Liquibase/Flyway → `DATABASECHANGELOG` テーブルに履歴を残す
    - 外部監査にも対応可能

# 大規模データのマイグレーション戦略

## 🎯 目的

-   数百万〜数億行規模のテーブルを **サービス停止なく安全に変更する**こと

## 理論的背景

-   **即時 ALTER = テーブルロック**
    -   MySQL では `ALTER TABLE` の多くはコピー＆スワップを伴い、長時間ロックがかかる
-   **レプリケーション遅延**
    -   Master で大量 DDL/DML が走ると Replica が追いつけなくなる
    -   結果として **読み専用トラフィックが古いデータを返す**

## 実現方法

1. **オンラインスキーマ変更 (OSC)**

    - サービス停止を避けるためのアプローチ
    - 方法:
        - gh-ost → binlog tail ＋非同期コピー
        - pt-osc → トリガーで差分同期

2. **分割マイグレーション**

    - 大規模カラム追加を複数ステップに分割
    - 例: 新カラム追加 → 並行書き込み → データ移行 → 切替

3. **非同期レプリケーションの考慮**

    - Replica 遅延がどの程度発生するかを常時監視
    - `SHOW SLAVE STATUS` の `Seconds_Behind_Master` をチェック

4. **ロールバック戦略**
    - 失敗時は即座に旧テーブルへ戻せるよう設計
    - gh-ost / pt-osc は `_gho`, `_old` などの一時テーブルを残す仕組みを持っている

# 実験目的

-   Laravel + MySQL (docker-compose) 環境で **gh-ost** と **pt-online-schema-change (pt-osc)** を比較検証する。
-   Replica 環境を構築し、**本番想定での挙動の違い**を確認する。

# フェーズ 1: Master 単独環境での実験

## gh-ost

-   カラム追加・リネーム・削除・複雑 DDL を実行。
-   特徴: 外部キー付きテーブルではエラー、`--allow-on-master` が必要。
-   実行時にエラーが出ると一時テーブル (`_gho`, `_ghc`, `_del`) が残る。

## pt-online-schema-change

-   同等操作を実行。
-   実行前は権限 (`PROCESS` 権限など) の設定が必要。
-   `--allow-on-master` を正しく指定する必要があった。

## 大量データ投入 (100 万件, admins テーブル)

-   pt-osc で体感的に時間がかかることを確認。
-   gh-ost はトリガーを使わないため軽快だった。

# フェーズ 2: Replica 環境構築

-   docker-compose に `mysql-replica` を追加し、Master とのレプリケーションを構築。
-   課題:
    -   `replica` ユーザーに REPLICATION 権限・SUPER 権限が必要。
    -   初期化時に Master のダンプ (`--master-data`) を使い、binlog 同期から開始。

# フェーズ 3: Replica 環境での実験

## gh-ost

-   Replica 経由で実行成功（`--allow-on-master` なし）。
-   一時テーブル (`_admins_gho`, `_admins_ghc`, `_admins_del`) が作成 → DROP → スワップ。
-   Master / Replica 双方で `ghost_test` カラム追加を確認。
-   `SHOW SLAVE STATUS\G` → `Seconds_Behind_Master: 0`（遅延なし）。

## pt-online-schema-change

-   Master で実行 → binlog 経由で Replica へ伝搬。
-   Master / Replica 双方で `osc_test` カラム追加を確認。
-   実行中に `SHOW SLAVE STATUS\G` で **`Seconds_Behind_Master: 1`** を観測（遅延発生）。

# gh-ost vs pt-online-schema-change 比較

| 項目           | gh-ost                                    | pt-online-schema-change   |
| -------------- | ----------------------------------------- | ------------------------- |
| 方式           | binlog tail + コピー + スワップ           | トリガー + テーブルコピー |
| Replica 必須   | ✅（本番想定）                            | ❌（Master 実行で OK）    |
| 外部キー対応   | ❌ 不可                                   | ✅（制約あり）            |
| 大量データ性能 | 高速（遅延ほぼゼロ）                      | 遅延発生しやすい          |
| 残存テーブル   | `_gho`, `_ghc`, `_del` が残ることあり     | `_old` テーブルが残る     |
| 権限要件       | SUPER または REPLICATION_SLAVE_ADMIN 必要 | PROCESS 権限など          |
| 運用適性       | 本番大規模環境向き                        | 小〜中規模向き            |

# 実験まとめ

## gh-ost

-   Replica 経由で安全に実行でき、遅延なし。
-   大規模テーブル向き。
-   権限要件がやや厳しい。

## pt-online-schema-change

-   Master で直接実行できるが、Replica に遅延が出やすい。
-   中小規模環境やツール未導入の環境で利用されやすい。

👉 本番運用を想定するなら **gh-ost** の方が適しているが、環境要件が揃わない場合には **pt-osc** が現実解になるケースもある。
