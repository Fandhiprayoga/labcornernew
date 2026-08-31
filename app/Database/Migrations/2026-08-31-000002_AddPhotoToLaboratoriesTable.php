<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToLaboratoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('laboratories', [
            'photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'name'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('laboratories', 'photo');
    }
}