<?php

namespace App\Models;

use CodeIgniter\Model;

class BhpItemModel extends Model
{
    protected $table = 'pengajuan_bhp_item';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['uuid', 'pengajuan_id', 'nama_barang', 'spesifikasi', 'qty', 'satuan', 'harga_satuan', 'total_harga', 'vendor', 'link_toko_online'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        helper('uuid');
        $data['data']['uuid'] = generate_uuid();
        return $data;
    }
}
