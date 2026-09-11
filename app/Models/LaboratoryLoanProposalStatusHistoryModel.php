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

    public function getApprovalHistory(int $perPage, ?int $changedBy, string $search = '', string $status = '', string $laboratoryUuid = ''): array
    {
        $query = $this->select('laboratory_loan_proposal_status_histories.*, laboratory_loan_proposals.uuid AS proposal_uuid, laboratory_loan_proposals.full_name, laboratory_loan_proposals.identity_number, laboratory_loan_proposals.event_name, laboratories.name AS laboratory_name, rooms.code AS room_code, rooms.name AS room_name, users.username AS changed_by_name')
            ->join('laboratory_loan_proposals', 'laboratory_loan_proposals.id = laboratory_loan_proposal_status_histories.proposal_id')
            ->join('laboratory_loan_proposal_items', 'laboratory_loan_proposal_items.proposal_id = laboratory_loan_proposals.id')
            ->join('laboratories', 'laboratories.id = laboratory_loan_proposal_items.laboratory_id')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
            ->join('users', 'users.id = laboratory_loan_proposal_status_histories.changed_by', 'left')
            ->whereIn('laboratory_loan_proposal_status_histories.to_status', ['laboran_approved', 'approved', 'rejected']);

        if ($changedBy !== null) {
            $query->where('laboratory_loan_proposal_status_histories.changed_by', $changedBy);
        }

        if ($laboratoryUuid !== '') {
            $query->where('laboratories.uuid', $laboratoryUuid);
        }

        if ($search !== '') {
            $query->groupStart()
                ->like('laboratory_loan_proposals.identity_number', $search)
                ->orLike('laboratory_loan_proposals.full_name', $search)
                ->orLike('laboratory_loan_proposals.event_name', $search)
                ->orLike('laboratories.name', $search)
                ->orLike('rooms.code', $search)
                ->orLike('rooms.name', $search)
                ->groupEnd();
        }

        if ($status !== '') {
            $query->where('laboratory_loan_proposal_status_histories.to_status', $status);
        }

        return $query->groupBy('laboratory_loan_proposal_status_histories.id')->orderBy('laboratory_loan_proposal_status_histories.created_at', 'DESC')->paginate($perPage);
    }
}
