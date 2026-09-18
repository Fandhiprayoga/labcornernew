<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPickupFieldsToAssetLoanProposalItems extends Migration
{
    public function up()
    {
        $fields = [
            'is_taken' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false],
            'taken_at' => ['type' => 'DATETIME', 'null' => true],
        ];

        $this->forge->addColumn('asset_loan_proposal_items', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('asset_loan_proposal_items', ['is_taken', 'taken_at']);
    }
}