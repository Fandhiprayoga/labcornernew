<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBhpPocketItemOwnership extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pengajuan_bhp_item', [
            'laboran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'pengajuan_id'],
            'laboratory_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'laboran_id'],
        ]);
        $this->forge->addKey('laboran_id', false, 'pengajuan_bhp_item');
        $this->forge->addKey('laboratory_id', false, 'pengajuan_bhp_item');
        $this->forge->addForeignKey('laboran_id', 'users', 'id', 'RESTRICT', 'CASCADE', 'pengajuan_bhp_item');
        $this->forge->addForeignKey('laboratory_id', 'laboratories', 'id', 'RESTRICT', 'CASCADE', 'pengajuan_bhp_item');
        $this->db->query('UPDATE pengajuan_bhp_item i JOIN pengajuan_bhp p ON p.id = i.pengajuan_id SET i.laboran_id = p.laboran_id, i.laboratory_id = p.laboratory_id WHERE i.laboran_id IS NULL');
    }

    public function down()
    {
        $this->forge->dropColumn('pengajuan_bhp_item', ['laboran_id', 'laboratory_id']);
    }
}