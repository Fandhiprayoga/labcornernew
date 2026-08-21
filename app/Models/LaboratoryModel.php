<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratoryModel extends Model
{
    protected $table          = 'laboratories';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = ['uuid', 'room_id', 'name', 'status', 'description'];
    protected $useTimestamps  = true;
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