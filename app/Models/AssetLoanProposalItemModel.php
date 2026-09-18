<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetLoanProposalItemModel extends Model
{
    protected $table = 'asset_loan_proposal_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'proposal_id', 'asset_id', 'notes', 'is_taken', 'taken_at', 'is_returned', 'returned_at', 'return_note'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }

    public function getCart(int $proposalId): array
    {
        return $this->select('asset_loan_proposal_items.*, assets.asset_code, assets.name AS asset_name, assets.photo, assets.category, assets.brand, assets.model, assets.laboratory_id, laboratories.name AS laboratory_name, rooms.code AS room_code')
            ->join('assets', 'assets.id = asset_loan_proposal_items.asset_id')
            ->join('laboratories', 'laboratories.id = assets.laboratory_id', 'left')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
            ->where('asset_loan_proposal_items.proposal_id', $proposalId)
            ->orderBy('asset_loan_proposal_items.id', 'ASC')
            ->findAll();
    }

    public function hasAllReturned(int $proposalId): bool
    {
        return $this->where('proposal_id', $proposalId)->where('is_returned', 0)->countAllResults() === 0;
    }

    public function hasAllTaken(int $proposalId): bool
    {
        return $this->where('proposal_id', $proposalId)->countAllResults() > 0
            && $this->where('proposal_id', $proposalId)->where('is_taken', 0)->countAllResults() === 0;
    }

    public function markReturned(int $proposalId, int $assetId, ?string $note = null): void
    {
        $this->where(['proposal_id' => $proposalId, 'asset_id' => $assetId])
            ->set([
                'is_returned' => 1,
                'returned_at' => date('Y-m-d H:i:s'),
                'return_note' => $note,
            ])
            ->update();
    }

    public function markUnreturned(int $proposalId, int $assetId): void
    {
        $this->where(['proposal_id' => $proposalId, 'asset_id' => $assetId])
            ->set([
                'is_returned' => 0,
                'returned_at' => null,
                'return_note' => null,
            ])
            ->update();
    }
}
