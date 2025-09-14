# トランザクション分離レベル 学習ノート

## 📘 トランザクション分離レベルとは

トランザクションが同時に実行されたときに、データをどのように読み書きするかを制御する仕組み。  
SQL 標準では以下の 4 レベルが定義されている。

---

## 🔍 分離レベルごとの特徴と現象

| 分離レベル           | Dirty Read | Non-Repeatable Read | Phantom Read | 説明                                           |
| -------------------- | ---------- | ------------------- | ------------ | ---------------------------------------------- |
| **READ UNCOMMITTED** | ✅ 発生    | ✅ 発生             | ✅ 発生      | 未コミットの更新を読めてしまう最も緩いレベル   |
| **READ COMMITTED**   | ❌ 防止    | ✅ 発生             | ✅ 発生      | 他トランザクションのコミット済み変更は読める   |
| **REPEATABLE READ**  | ❌ 防止    | ❌ 防止             | ✅ 発生      | 同じクエリは同じ結果を返すが、新しい行は見える |
| **SERIALIZABLE**     | ❌ 防止    | ❌ 防止             | ❌ 防止      | 完全に直列化して実行される。ブロックが多発する |

---

## 🧪 現象の定義

| 現象名                  | 定義                                                   |
| ----------------------- | ------------------------------------------------------ |
| **Dirty Read**          | 他トランザクションの未コミットの変更を読み取ってしまう |
| **Non-Repeatable Read** | 同じクエリを 2 回実行したときに結果が異なる            |
| **Phantom Read**        | 同じ条件のクエリで結果セットの行数が変わる             |

---

## ✅ 実験結果まとめ

### 1. READ UNCOMMITTED

- **初回 SELECT**: `balance = 100`
- **T2 UPDATE（未 COMMIT）**: `balance = 999`
- **再度 SELECT**: `balance = 999`（未コミットの値が見えた → Dirty Read）
- **T2 ROLLBACK**: 値は戻るが、T1 は「幻の値」を読んでいた

### 2. READ COMMITTED

- **初回 SELECT**: `balance = 100`
- **T2 UPDATE → COMMIT**: `balance = 999`
- **再度 SELECT**: `balance = 999`（Non-Repeatable Read 発生）

### 3. REPEATABLE READ

- **初回 SELECT**: `balance = 100`
- **T2 UPDATE → COMMIT**: `balance = 999`
- **再度 SELECT**: `balance = 100`（同じ結果を返す → Non-Repeatable Read 防止）
- **T2 INSERT → COMMIT**: 新しい行は T1 に見えない（スナップショット固定）

### 4. SERIALIZABLE

- **初回 SELECT count = 10**
- **T2 INSERT**: 🚧 ブロックされる
- **再度 SELECT**: `count = 10`（Phantom Read 防止）
- **T1 COMMIT 後**: T2 の INSERT が反映される

---

## 🎯 学びのポイント

1. **分離レベルが上がるほど、一貫性は強くなるが同時実行性は下がる**

   - READ UNCOMMITTED: 高速だが危険
   - SERIALIZABLE: 安全だがスループット低下

2. **MySQL のデフォルトは REPEATABLE READ**

   - 多くのアプリでは十分だが、Phantom Read が問題になる場合は SERIALIZABLE を検討

3. **アプリ設計では「どの現象を防ぎたいか」で分離レベルを選択する必要がある**

---

## 📌 まとめ

- Dirty Read, Non-Repeatable Read, Phantom Read を **実験で体感** できた
- MySQL (InnoDB) の分離レベルの挙動を **理論と実践の両面から理解** できた
- チームで共通理解を持つために、このノートを知見として活用できる
