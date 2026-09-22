<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBhpItemOverrideAudit extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'pengajuan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'changed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'before_data' => ['type' => 'JSON'],
            'after_data' => ['type' => 'JSON'],
            'reason' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey(['item_id', 'created_at']);
        $this->forge->addForeignKey('item_id', 'pengajuan_bhp_item', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('pengajuan_id', 'pengajuan_bhp', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('pengajuan_bhp_item_override');
    }

    public function down()
    {
        $this->forge->dropTable('pengajuan_bhp_item_override', true);
    }
}