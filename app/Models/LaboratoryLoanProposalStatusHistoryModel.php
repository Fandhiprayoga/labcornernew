<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratoryLoanProposalStatusHistoryModel extends Model
{
    protected $table         = 'laboratory_loan_proposal_status_histories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['uuid', 'proposal_id', 'from_status', 'to_status', 'changed_by', 'note'];
    protected $useTimestamps = true;
    protected $beforeInsert  = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }

    public function record(int $proposalId, ?string $fromStatus, string $toStatus, ?string $note = null): void
    {
        $this->insert([
            'proposal_id' => $proposalId,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'changed_by'  => auth()->id(),
            'note'        => $note,
        ]);
    }

    public function getForProposal(int $proposalId): array
    {
        return $this->select('laboratory_loan_proposal_status_histories.*, users.username AS changed_by_name')
            ->join('users', 'users.id = laboratory_loan_proposal_status_histories.changed_by', 'left')
            ->where('proposal_id', $proposalId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
