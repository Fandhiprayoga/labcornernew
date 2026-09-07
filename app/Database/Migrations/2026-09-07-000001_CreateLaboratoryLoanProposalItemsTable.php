<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaboratoryLoanProposalItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'          => ['type' => 'CHAR', 'constraint' => 36],
            'proposal_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'laboratory_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'notes'         => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey(['proposal_id', 'laboratory_id']);
        $this->forge->addKey('laboratory_id');
        $this->forge->addForeignKey('proposal_id', 'laboratory_loan_proposals', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('laboratory_id', 'laboratories', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('laboratory_loan_proposal_items');
    }

    public function down()
    {
        $this->forge->dropTable('laboratory_loan_proposal_items', true);
    }
}
