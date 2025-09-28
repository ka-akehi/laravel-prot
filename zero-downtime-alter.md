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
