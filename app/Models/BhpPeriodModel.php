<?php

namespace App\Models;

use CodeIgniter\Model;

class BhpPeriodModel extends Model
{
    protected $table = 'periode_pengajuan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai', 'created_by'];
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

    public function active(?string $at = null): ?array
    {
        $at ??= date('Y-m-d H:i:s');
        return $this->where('tanggal_mulai <=', $at)->where('tanggal_selesai >=', $at)->orderBy('tanggal_mulai', 'DESC')->first();
    }
}
