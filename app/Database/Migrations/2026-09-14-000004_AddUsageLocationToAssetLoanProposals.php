<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsageLocationToAssetLoanProposals extends Migration
{
    public function up()
    {
        $this->forge->addColumn('asset_loan_proposals', [
            'usage_location' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'event_end'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('asset_loan_proposals', 'usage_location');
    }
}
