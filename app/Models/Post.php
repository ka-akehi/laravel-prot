<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // 明示的にfillableを定義（Mass Assignmentを許可）
    protected $fillable = [
        'user_id',
        'title',
        'body',
    ];

    /**
     * 投稿の所有ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
