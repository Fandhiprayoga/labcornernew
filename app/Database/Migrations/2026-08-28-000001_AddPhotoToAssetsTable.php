<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToAssetsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('assets', [
            'photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'name'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('assets', 'photo');
    }
}
