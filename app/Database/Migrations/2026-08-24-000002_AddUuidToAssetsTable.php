<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUuidToAssetsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('assets', [
            'uuid' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => true,
                'after' => 'id',
            ],
        ]);

        helper('uuid');

        $assets = $this->db->table('assets')->select('id')->get()->getResultArray();

        foreach ($assets as $asset) {
            $this->db->table('assets')
                ->where('id', $asset['id'])
                ->update(['uuid' => generate_uuid()]);
        }

        $this->forge->modifyColumn('assets', [
            'uuid' => [
                'name' => 'uuid',
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
        ]);

        $this->forge->addUniqueKey('uuid');
    }

    public function down()
    {
        $this->forge->dropColumn('assets', 'uuid');
    }
}