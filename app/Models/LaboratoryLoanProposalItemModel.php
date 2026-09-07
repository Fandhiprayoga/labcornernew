<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratoryLoanProposalItemModel extends Model
{
    protected $table          = 'laboratory_loan_proposal_items';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = ['uuid', 'proposal_id', 'laboratory_id', 'notes'];
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $beforeInsert   = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();

        return $data;
    }

    public function findByUuid(string $uuid): ?array
    {
        return $this->where('uuid', $uuid)->first();
    }

    /**
     * Cart items of a proposal, enriched with laboratory and room information.
     */
    public function getCart(int $proposalId): array
    {
        return $this->select('laboratory_loan_proposal_items.*, laboratories.name AS laboratory_name, laboratories.photo AS laboratory_photo, rooms.code AS room_code, rooms.name AS room_name, rooms.building, rooms.floor, rooms.capacity')
            ->join('laboratories', 'laboratories.id = laboratory_loan_proposal_items.laboratory_id')
            ->join('rooms', 'rooms.id = laboratories.room_id')
            ->where('laboratory_loan_proposal_items.proposal_id', $proposalId)
            ->orderBy('laboratory_loan_proposal_items.id', 'ASC')
            ->findAll();
    }
}
