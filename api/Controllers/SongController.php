<?php
namespace Controllers;

require_once __DIR__ . '/../bootstrap.php';

use Models\Song;

class SongController
{
    /**
     * 曲一覧を取得（認証不要）
     */
    public function list(): void
    {
        try {
            // singer_id → id でソートして取得
            $songs = Song::orderBy('singer_id')
                ->orderBy('id')
                ->get(['id', 'singer_id', 'name', 'youtube_url'])
                ->toArray();

            // singer_idごとにグループ化
            $grouped = [];
            foreach ($songs as $song) {
                $singerId = $song['singer_id'];
                unset($song['singer_id']); // レスポンスには不要なら外す

                $grouped[$singerId][] = $song;
            }

            success($grouped);
        } catch (\Throwable $e) {
            error('曲一覧の取得に失敗しました: ' . $e->getMessage(), 500);
        }
    }
}
