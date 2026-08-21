<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUuidToLaboratoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('laboratories', [
            'uuid' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true, 'after' => 'id'],
        ]);

        $this->backfillUuids();

        $this->db->query('ALTER TABLE laboratories MODIFY uuid CHAR(36) NOT NULL');
        $this->db->query('ALTER TABLE laboratories ADD UNIQUE KEY laboratories_uuid_unique (uuid)');
    }

    public function down()
    {
        $this->forge->dropColumn('laboratories', 'uuid');
    }

    private function backfillUuids(): void
    {
        helper('uuid');

        $rows = $this->db->table('laboratories')->select('id')->get()->getResultArray();

        foreach ($rows as $row) {
            $this->db->table('laboratories')
                ->where('id', $row['id'])
                ->update(['uuid' => generate_uuid()]);
        }
    }
}
