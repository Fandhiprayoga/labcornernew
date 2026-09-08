<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaboratoryLoanProposalStatusHistoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'        => ['type' => 'CHAR', 'constraint' => 36],
            'proposal_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'from_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'to_status'   => ['type' => 'VARCHAR', 'constraint' => 20],
            'changed_by'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'note'        => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('proposal_id');
        $this->forge->addForeignKey('proposal_id', 'laboratory_loan_proposals', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('laboratory_loan_proposal_status_histories');
    }

    public function down()
    {
        $this->forge->dropTable('laboratory_loan_proposal_status_histories', true);
    }
}
