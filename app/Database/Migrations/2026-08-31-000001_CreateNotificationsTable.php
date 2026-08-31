<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'       => ['type' => 'CHAR', 'constraint' => 36],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'module'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'info'],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'message'    => ['type' => 'TEXT', 'null' => true],
            'data'       => ['type' => 'TEXT', 'null' => true],
            'url'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_read'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'read_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('user_id');
        $this->forge->addKey('is_read');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications', true);
    }
}
