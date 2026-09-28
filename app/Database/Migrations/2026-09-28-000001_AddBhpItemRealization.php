<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBhpItemRealization extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pengajuan_bhp_item', [
            'realisasi_biaya' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true, 'after' => 'total_harga'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pengajuan_bhp_item', 'realisasi_biaya');
    }
}
