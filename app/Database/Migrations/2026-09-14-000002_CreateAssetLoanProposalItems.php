<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetLoanProposalItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true], 'uuid' => ['type' => 'CHAR', 'constraint' => 36], 'proposal_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], 'asset_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], 'notes' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true], 'created_at' => ['type' => 'DATETIME', 'null' => true], 'updated_at' => ['type' => 'DATETIME', 'null' => true], 'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true); $this->forge->addUniqueKey('uuid'); $this->forge->addUniqueKey(['proposal_id', 'asset_id']); $this->forge->addKey('asset_id'); $this->forge->addForeignKey('proposal_id', 'asset_loan_proposals', 'id', 'CASCADE', 'CASCADE'); $this->forge->addForeignKey('asset_id', 'assets', 'id', 'RESTRICT', 'CASCADE'); $this->forge->createTable('asset_loan_proposal_items');
    }

    public function down() { $this->forge->dropTable('asset_loan_proposal_items', true); }
}
