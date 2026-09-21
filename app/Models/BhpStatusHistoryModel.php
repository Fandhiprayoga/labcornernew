<?php

namespace App\Models;

use CodeIgniter\Model;

class BhpStatusHistoryModel extends Model
{
    protected $table = 'pengajuan_bhp_status_history';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'pengajuan_id', 'from_status', 'to_status', 'changed_by', 'note', 'created_at'];
    protected $useTimestamps = false;
    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }
}
