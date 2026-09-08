<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillLaboratoryLoanProposalStatusHistories extends Migration
{
    public function up()
    {
        $proposals = $this->db->table('laboratory_loan_proposals')
            ->select('id, status, created_at')
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getResultArray();

        foreach ($proposals as $proposal) {
            $this->db->table('laboratory_loan_proposal_status_histories')->insert([
                'uuid'        => sprintf('%s-%s-%s-%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(6))),
                'proposal_id' => $proposal['id'],
                'from_status' => null,
                'to_status'   => $proposal['status'],
                'changed_by'  => null,
                'note'        => 'Riwayat awal proposal.',
                'created_at'  => $proposal['created_at'],
                'updated_at'  => $proposal['created_at'],
            ]);
        }
    }

    public function down()
    {
        $this->db->table('laboratory_loan_proposal_status_histories')
            ->where('note', 'Riwayat awal proposal.')
            ->delete();
    }
}
