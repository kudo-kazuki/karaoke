<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSongsTable extends AbstractMigration
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
        $table = $this->table('songs');

        $table
            ->addColumn('singer_id', 'integer', ['null' => false]) // 後で外部キー制約つける
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('youtube_url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('release_date', 'date', ['null' => false])
            ->addColumn('lyrics', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->create();
    }
}
