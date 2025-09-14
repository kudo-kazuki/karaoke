<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SingerSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        $data = [
            ['name' => 'GLAY', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'L\'Arc〜en〜Ciel', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'X JAPAN', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '浜崎あゆみ', 'gender' => 2, 'is_group' => false, 'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '松浦亜弥', 'gender' => 2, 'is_group' => false, 'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'T.M.Revolution', 'gender' => 1, 'is_group' => false, 'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'KinKi Kids', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ポルノグラフィティ', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'JUDY AND MARY', 'gender' => 2, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mr.Children',  'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Janne Da Arc', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '大塚愛', 'gender' => 2, 'is_group' => false, 'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'vtuber、その他', 'gender' => 3, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'アニソン', 'gender' => 2, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'SPEED', 'gender' => 2, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ゆず', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'TOKIO/SMAP', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'シド',  'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B’z', 'gender' => 1, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'AKB48', 'gender' => 2, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'モーニング娘', 'gender' => 2, 'is_group' => true,  'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'YUKI', 'gender' => 2, 'is_group' => false, 'debut_date' => $today, 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->table('singers')->insert($data)->saveData();
    }
}
