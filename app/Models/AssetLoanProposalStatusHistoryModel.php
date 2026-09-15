<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetLoanProposalStatusHistoryModel extends Model
{
    protected $table = 'asset_loan_proposal_status_histories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'proposal_id', 'from_status', 'to_status', 'changed_by', 'note'];
    protected $useTimestamps = true;
    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }

    public function record(int $proposalId, ?string $from, string $to, ?string $note = null): void
    {
        $this->insert(['proposal_id' => $proposalId, 'from_status' => $from, 'to_status' => $to, 'changed_by' => auth()->id(), 'note' => $note]);
    }

    public function getForProposal(int $proposalId): array
    {
        return $this->select('asset_loan_proposal_status_histories.*, users.username AS changed_by_name')
            ->join('users', 'users.id = asset_loan_proposal_status_histories.changed_by', 'left')
            ->where('proposal_id', $proposalId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function getApprovalHistory(int $perPage, ?int $changedBy, string $search = '', string $status = ''): array
    {
        $query = $this->select('asset_loan_proposal_status_histories.*, asset_loan_proposals.uuid AS proposal_uuid, asset_loan_proposals.full_name, asset_loan_proposals.identity_number, asset_loan_proposals.event_name, users.username AS changed_by_name')
            ->join('asset_loan_proposals', 'asset_loan_proposals.id = asset_loan_proposal_status_histories.proposal_id')
            ->join('users', 'users.id = asset_loan_proposal_status_histories.changed_by', 'left')
            ->whereIn('asset_loan_proposal_status_histories.to_status', ['approved', 'rejected']);
        if ($changedBy !== null) $query->where('asset_loan_proposal_status_histories.changed_by', $changedBy);
        if ($status !== '') $query->where('asset_loan_proposal_status_histories.to_status', $status);
        if ($search !== '') $query->groupStart()->like('asset_loan_proposals.identity_number', $search)->orLike('asset_loan_proposals.full_name', $search)->orLike('asset_loan_proposals.event_name', $search)->groupEnd();
        return $query->orderBy('asset_loan_proposal_status_histories.created_at', 'DESC')->paginate($perPage);
    }
}
