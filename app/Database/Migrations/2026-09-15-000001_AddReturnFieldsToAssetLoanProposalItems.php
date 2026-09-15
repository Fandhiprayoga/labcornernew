<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReturnFieldsToAssetLoanProposalItems extends Migration
{
    public function up()
    {
        $fields = [
            'is_returned' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false],
            'returned_at' => ['type' => 'DATETIME', 'null' => true],
            'return_note' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
        ];

        $this->forge->addColumn('asset_loan_proposal_items', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('asset_loan_proposal_items', ['is_returned', 'returned_at', 'return_note']);
    }
}
