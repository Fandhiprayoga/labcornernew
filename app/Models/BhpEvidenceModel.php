<?php

namespace App\Models;

use CodeIgniter\Model;

class BhpEvidenceModel extends Model
{
    protected $table = 'pengajuan_bhp_eviden';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'pengajuan_id', 'item_id', 'tipe_file', 'file_path', 'original_name', 'uploaded_by', 'uploaded_at'];
    protected $useTimestamps = false;
    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }
}
