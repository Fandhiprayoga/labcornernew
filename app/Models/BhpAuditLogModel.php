<?php

namespace App\Models;

use CodeIgniter\Model;

class BhpAuditLogModel extends Model
{
    protected $table = 'audit_log_admin';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'admin_id', 'action_type', 'target_entity_id', 'notes', 'created_at'];
    protected $useTimestamps = false;
    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }
}
