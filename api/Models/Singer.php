<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Singer extends Model
{
    protected $table = 'singers';    // テーブル名
    protected $primaryKey = 'id';    // 主キー
    public $timestamps = true;       // created_at, updated_at 自動管理

    protected $fillable = [
        'name',
        'gender',
        'is_group',
        'debut_date',
        'description',
    ];

    // Create用ルール
    public static array $createRules = [
        'name'        => 'required|max:255',
        'gender'      => 'numeric|nullable',        // 1,2,3 または null
        'is_group'    => 'required|numeric',        // booleanだが0/1で送られてくるのでnumeric
        'debut_date'  => 'required|date',
        'description' => 'max:1000|nullable',
    ];

    // Edit用ルール
    public static array $editRules = [
        'id'          => 'required|numeric',
        'name'        => 'required|max:255',
        'gender'      => 'numeric|nullable',
        'is_group'    => 'required|numeric',
        'debut_date'  => 'required|date',
        'description' => 'max:1000|nullable',
    ];

    public static array $deleteRules = [
        'id' => 'required|numeric',
    ];

    public function songs()
    {
        return $this->hasMany(Song::class, 'singer_id');
    }
}
