<?php

namespace App\Models;

use CodeIgniter\Model;

class BhpLaboranSubmissionModel extends Model
{
    protected $table = 'pengajuan_bhp_laboran_submission';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'pengajuan_id', 'laboran_id', 'submitted_at'];
    protected $useTimestamps = false;
    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }
}
