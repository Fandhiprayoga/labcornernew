<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBhpLaboranSubmissions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'pengajuan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'laboran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'submitted_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey(['pengajuan_id', 'laboran_id']);
        $this->forge->addKey('laboran_id');
        $this->forge->addForeignKey('pengajuan_id', 'pengajuan_bhp', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('laboran_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('pengajuan_bhp_laboran_submission');
    }

    public function down()
    {
        $this->forge->dropTable('pengajuan_bhp_laboran_submission', true);
    }
}
