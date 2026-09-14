<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetLoanProposalModel extends Model
{
    protected $table = 'asset_loan_proposals';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'user_id', 'identity_number', 'full_name', 'phone', 'email', 'proposal_date', 'event_name', 'event_start', 'event_end', 'usage_location', 'acknowledgement', 'status'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $beforeInsert = ['generateUuid'];

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
}
