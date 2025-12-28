<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSortOrderToSongs extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $this->table('songs')
            ->addColumn('sort_order', 'integer', [
                'null' => false,
                'default' => 0,
                'comment' => '歌手内での表示順'
            ])
            ->update();
    }
}
