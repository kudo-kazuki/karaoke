<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';      // テーブル名
    protected $primaryKey = 'id';     // 主キー
    public $timestamps = true;        // created_at, updated_at 自動管理

    // パスワードなど隠したいカラム
    protected $hidden = ['password'];

    protected $fillable = ['name', 'password', 'level', 'remarks'];

    // Create用ルール
    public static array $createRules = [
        'name' => 'required|max:50',
        'password' => 'required|max:255',
        'level' => 'required|numeric',
        'remarks' => 'max:255|nullable',
    ];

    // Edit用ルール
    public static array $editRules = [
        'id' => 'required|numeric',
        'name' => 'required|max:50',
        'level' => 'required|numeric',
        'remarks' => 'max:255|nullable',
        'password' => 'max:255|nullable',
    ];

    public static array $deleteRules = [
        'id' => 'required|numeric',
    ];
}
