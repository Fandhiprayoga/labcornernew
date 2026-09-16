<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeLaboratoryLoanProposalDateToDatetime extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('laboratory_loan_proposals', [
            'proposal_date' => ['type' => 'DATETIME'],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('laboratory_loan_proposals', [
            'proposal_date' => ['type' => 'DATE'],
        ]);
    }
}
