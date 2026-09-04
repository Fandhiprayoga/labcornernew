<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaboratoryLoanProposalsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'            => ['type' => 'CHAR', 'constraint' => 36],
            'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'identity_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'full_name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 30],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'proposal_date'   => ['type' => 'DATE'],
            'event_name'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'event_start'     => ['type' => 'DATETIME'],
            'event_end'       => ['type' => 'DATETIME'],
            'acknowledgement' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('laboratory_loan_proposals');
    }

    public function down()
    {
        $this->forge->dropTable('laboratory_loan_proposals', true);
    }
}