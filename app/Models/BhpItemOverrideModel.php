<?php

namespace App\Models;

use CodeIgniter\Model;

class BhpItemOverrideModel extends Model
{
    protected $table = 'pengajuan_bhp_item_override';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'item_id', 'pengajuan_id', 'changed_by', 'before_data', 'after_data', 'reason', 'created_at'];
    protected $useTimestamps = false;
    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }
}