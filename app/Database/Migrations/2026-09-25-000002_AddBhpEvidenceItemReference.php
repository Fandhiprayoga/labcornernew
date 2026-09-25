<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBhpEvidenceItemReference extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pengajuan_bhp_eviden', [
            'item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'pengajuan_id'],
        ]);
        $this->forge->addKey('item_id', false, 'pengajuan_bhp_eviden');
        $this->forge->addForeignKey('item_id', 'pengajuan_bhp_item', 'id', 'CASCADE', 'CASCADE', 'pengajuan_bhp_eviden');
    }

    public function down()
    {
        $this->forge->dropColumn('pengajuan_bhp_eviden', 'item_id');
    }
}