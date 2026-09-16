<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdentityNumberToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'identity_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'phone'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'identity_number');
    }
}