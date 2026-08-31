<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table          = 'notifications';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = [
        'uuid',
        'user_id',
        'module',
        'type',
        'title',
        'message',
        'data',
        'url',
        'is_read',
        'read_at',
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

    public function forUser(int $userId, bool $unreadOnly = false)
    {
        $builder = $this->where('user_id', $userId);

        if ($unreadOnly) {
            $builder = $builder->where('is_read', 0);
        }

        return $builder->orderBy('created_at', 'DESC');
    }

    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }
}
