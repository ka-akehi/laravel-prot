# データの整合性を保証する実装まとめ

## DB の整合性を保証する

- 外部キー制約
  - 不正な外部ID挿入を防止
  - 親レコード削除時に子レコードも削除（cascade）

```php
$table->foreignId('user_id')->constrained()->onDelete('cascade');
```

- NOT NULL、UNIQUE、DEFAULT 制約など
  - データの重複・NULLをDB側で防ぐ
  - Laravelのバリデーションをすり抜けてもDBが守る

```php
$table->string('email')->unique()->notNullable();
```

- トランザクション
  - 途中で失敗した場合に全体をロールバック
  - 特に「在庫 - 注文」など一連の処理に必須

```php
DB::transaction(function () {
    // 複数テーブル操作の一貫性を担保
});
```

## アプリケーション側での整合性

- バリデーション
  - 不正データの入力をコントローラレベルでブロック
  - ルールの組み合わせで柔軟に制御

```php
$request->validate([
    'email' => ['required', 'email', 'unique:users,email'],
]);
```

- Eloquentリレーション
  - 関連性を明示し、データアクセス時の設計ミスを防ぐ

```php
$user->posts(); // hasMany
$post->user();  // belongsTo
```

- モデルイベントやObserver
  - 削除・更新のタイミングで連動処理を定義できる

```
// 親テーブルのdelete時に実行される
public static function booted()
{
    static::deleting(function ($user) {
        // 子テーブルのデータも削除など
    });
}
```

## 論理削除

- 間違って削除しても復元可能
- ユーザーが参照すべきデータだけを保持可能

```php
use Illuminate\Database\Eloquent\SoftDeletes;

$table->softDeletes(); // カラムに deleted_at を追加
```

## 設計習慣

- リポジトリパターン
  - ビジネスロジックとデータアクセスを分離

- DTO / FormRequest
  - 入力整形とバリデーションの分離

- ドメインモデルの集約設計
  - 一貫性のある単位で更新できる設計
