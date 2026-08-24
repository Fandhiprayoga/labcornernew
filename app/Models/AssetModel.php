<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetModel extends Model
{
    public const STATUS_READY = 'ready';
    public const STATUS_BORROWED = 'borrowed';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_DAMAGED = 'damaged';
    public const STATUS_LOST = 'lost';
    public const STATUS_RETIRED = 'retired';

    protected $table          = 'assets';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = [
        'uuid',
        'asset_code',
        'name',
        'laboratory_id',
        'category',
        'brand',
        'model',
        'serial_number',
        'acquisition_date',
        'purchase_price',
        'can_be_borrowed',
        'status',
        'description',
    ];
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

    public static function statuses(): array
    {
        return [
            self::STATUS_READY => 'Ready',
            self::STATUS_BORROWED => 'Sedang Dipinjam',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_DAMAGED => 'Rusak',
            self::STATUS_LOST => 'Hilang',
            self::STATUS_RETIRED => 'Tidak Digunakan',
        ];
    }

    public static function statusBadges(): array
    {
        return [
            self::STATUS_READY => 'success',
            self::STATUS_BORROWED => 'warning',
            self::STATUS_MAINTENANCE => 'info',
            self::STATUS_DAMAGED => 'danger',
            self::STATUS_LOST => 'danger',
            self::STATUS_RETIRED => 'secondary',
        ];
    }
}