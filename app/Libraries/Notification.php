<?php

namespace App\Libraries;

use App\Models\NotificationModel;
use CodeIgniter\Shield\Models\GroupModel;

/**
 * Library notifikasi generik yang dapat dipakai oleh semua modul
 * untuk menyimpan dan membaca notifikasi user.
 */
class Notification
{
    protected NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    /**
     * Kirim notifikasi ke satu user.
     *
     * @param array{module?: string, type?: string, url?: string, data?: array} $options
     */
    public function send(int $userId, string $title, string $message = '', array $options = []): array
    {
        $payload = [
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'module'  => $options['module'] ?? null,
            'type'    => $options['type'] ?? 'info',
            'url'     => $options['url'] ?? null,
            'data'    => isset($options['data']) ? json_encode($options['data']) : null,
        ];

        $this->model->insert($payload);

        return $this->model->find($this->model->getInsertID());
    }

    /**
     * Kirim notifikasi yang sama ke banyak user sekaligus.
     *
     * @param list<int> $userIds
     */
    public function sendToMany(array $userIds, string $title, string $message = '', array $options = []): void
    {
        foreach ($userIds as $userId) {
            $this->send($userId, $title, $message, $options);
        }
    }

    /**
     * Kirim notifikasi ke seluruh user yang memiliki role/group tertentu.
     */
    public function sendToRole(string $group, string $title, string $message = '', array $options = []): void
    {
        $groupModel = new GroupModel();
        $userIds    = $groupModel->builder()
            ->select('user_id')
            ->where('group', $group)
            ->get()
            ->getResultArray();

        $userIds = array_unique(array_column($userIds, 'user_id'));

        if (! empty($userIds)) {
            $this->sendToMany($userIds, $title, $message, $options);
        }
    }

    /**
     * Ambil daftar notifikasi milik seorang user.
     */
    public function getForUser(int $userId, int $limit = 10, bool $unreadOnly = false): array
    {
        return $this->model->forUser($userId, $unreadOnly)
            ->findAll($limit);
    }

    /**
     * Hitung jumlah notifikasi yang belum dibaca.
     */
    public function unreadCount(int $userId): int
    {
        return $this->model->countUnread($userId);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     * Jika $userId diberikan, hanya menandai bila notifikasi milik user tersebut.
     */
    public function markAsRead(int $id, ?int $userId = null): bool
    {
        $notification = $this->model->find($id);

        if (! $notification) {
            return false;
        }

        if ($userId !== null && (int) $notification['user_id'] !== $userId) {
            return false;
        }

        return $this->model->update($id, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Tandai semua notifikasi milik user sebagai sudah dibaca.
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('is_read', 0)
            ->set([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }

    /**
     * Hapus satu notifikasi.
     */
    public function delete(int $id): bool
    {
        return (bool) $this->model->delete($id);
    }

    /**
     * Hapus semua notifikasi milik user.
     */
    public function deleteAllForUser(int $userId): bool
    {
        return (bool) $this->model->where('user_id', $userId)->delete();
    }
}
