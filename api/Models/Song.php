<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $table = 'songs';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'singer_id',
        'name',
        'youtube_url',
        'release_date',
        'lyrics',
    ];

    /** Create用ルール */
    public static array $createRules = [
        'singer_id'     => 'required|numeric',
        'name'          => 'required|max:255',
        'youtube_url'   => 'required|max:255',
        'release_date'  => 'date|nullable',
        'lyrics'        => 'max:65535|nullable',
    ];

    /** Edit用ルール */
    public static array $editRules = [
        'id'            => 'required|numeric',
        'singer_id'     => 'required|numeric',
        'name'          => 'required|max:255',
        'youtube_url'   => 'required|max:255',
        'release_date'  => 'date|nullable',
        'lyrics'        => 'max:65535|nullable',
    ];

    public static array $deleteRules = [
        'id' => 'required|numeric',
    ];

    /** リレーション */
    public function singer()
    {
        return $this->belongsTo(Singer::class, 'singer_id');
    }
}
