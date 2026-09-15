<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeAssetLoanProposalDateToDatetime extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('asset_loan_proposals', [
            'proposal_date' => ['type' => 'DATETIME'],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('asset_loan_proposals', [
            'proposal_date' => ['type' => 'DATE'],
        ]);
    }
}
