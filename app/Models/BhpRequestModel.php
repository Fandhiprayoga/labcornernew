<?php

namespace App\Models;

use CodeIgniter\Model;

class BhpRequestModel extends Model
{
    protected $table = 'pengajuan_bhp';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'kode_pengajuan', 'periode_id', 'laboran_id', 'laboratory_id', 'study_program_id', 'nama_lab_snapshot', 'prodi_snapshot', 'grand_total_estimasi', 'status', 'catatan_revisi', 'alasan_penolakan', 'nominal_cair', 'tanggal_cair', 'tanggal_belanja', 'realisasi_biaya', 'catatan_pembelian', 'catatan_verifikasi'];
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
