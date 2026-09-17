<?php

namespace App\Libraries;

use App\Models\NotificationModel;

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
        $userIds = $this->userIdsByGroup($group);

        if (! empty($userIds)) {
            $this->sendToMany($userIds, $title, $message, $options);
        }
    }

    /**
     * Notifikasi proposal yang diajukan ke laboran yang ditugaskan pada lab proposal.
     */
    public function sendProposalSubmittedToLaborans(int $proposalId, string $eventName, string $url): void
    {
        $userIds = $this->userIdsForAssignedLaborans($proposalId);

        if (empty($userIds)) {
            return;
        }

        $this->sendToMany($userIds, 'Pengajuan peminjaman menunggu persetujuan',
            "Pengajuan kegiatan {$eventName} menunggu persetujuan laboran.",
            ['url' => $url, 'type' => 'warning', 'module' => 'loan_proposal']
        );
    }

    public function sendProposalSubmittedToReviewers(int $proposalId, string $eventName, string $url): void
    {
        $this->sendProposalSubmittedToLaborans($proposalId, $eventName, $url);
    }

    /**
     * Notifikasi proposal peminjaman asset yang diajukan, dikirim ke laboran terkait dan kepala lab.
     */
    public function sendAssetProposalSubmittedToReviewers(int $proposalId, string $eventName, string $url): void
    {
        $userIds = $this->userIdsForAssetProposalReview($proposalId);

        if (empty($userIds)) {
            return;
        }

        $this->sendToMany($userIds, 'Pengajuan peminjaman asset menunggu persetujuan',
            "Pengajuan kegiatan {$eventName} menunggu persetujuan laboran dan kepala laboratorium.",
            ['url' => $url, 'type' => 'warning', 'module' => 'asset_loan_proposal']
        );
    }

    /**
     * Notifikasi persetujuan yang dibutuhkan oleh kepala lab setelah laboran menyetujui.
     */
    public function sendApprovalNeededToHeadLab(string $eventName, string $url): void
    {
        $this->sendToRole('kepala_lab', 'Pengajuan menunggu persetujuan kepala lab',
            "Pengajuan kegiatan {$eventName} telah disetujui laboran, menunggu keputusan kepala lab.",
            ['url' => $url, 'type' => 'info', 'module' => 'loan_proposal']
        );
    }

    /**
     * Notifikasi hasil keputusan proposal ke pemohon.
     */
    public function sendProposalDecisionToApplicant(int $userId, string $eventName, bool $approved, string $url, ?string $reason = null, string $module = 'loan_proposal'): void
    {
        $statusText = $approved ? 'disetujui' : 'ditolak';
        $message    = "Pengajuan kegiatan {$eventName} {$statusText}.";

        if ($reason !== null && trim($reason) !== '') {
            $message .= ' Alasan: ' . trim($reason);
        }

        $this->send($userId, $approved ? 'Pengajuan Anda disetujui' : 'Pengajuan Anda ditolak', $message, [
            'url' => $url,
            'type' => $approved ? 'success' : 'danger',
            'module' => $module,
        ]);
    }

    public function sendProposalCompletedToApplicant(int $userId, string $eventName, string $url): void
    {
        $this->send($userId, 'Pengajuan peminjaman selesai',
            "Peminjaman untuk kegiatan {$eventName} telah ditandai selesai.", [
                'url' => $url,
                'type' => 'success',
                'module' => 'loan_proposal',
            ]);
    }

    public function sendProposalCancelledToApplicant(int $userId, string $eventName, string $url, string $reason): void
    {
        $this->send($userId, 'Pengajuan peminjaman dibatalkan',
            "Pengajuan kegiatan {$eventName} dibatalkan. Alasan: " . trim($reason), [
                'url' => $url,
                'type' => 'danger',
                'module' => 'loan_proposal',
            ]);
    }

    public function sendAssetProposalCancelledToApplicant(int $userId, string $eventName, string $url, string $reason): void
    {
        $this->send($userId, 'Pengajuan peminjaman asset dibatalkan',
            "Pengajuan kegiatan {$eventName} dibatalkan. Alasan: " . trim($reason), [
                'url' => $url,
                'type' => 'danger',
                'module' => 'asset_loan_proposal',
            ]);
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

    protected function userIdsByGroup(string $group): array
    {
        $db = db_connect();

        if (! $db) {
            return [];
        }

        $result = $db->query(
            'SELECT DISTINCT user_id FROM auth_groups_users WHERE `group` = ? ',
            [$group]
        );

        if (! $result || ! method_exists($result, 'getResultArray')) {
            return [];
        }

        $rows = $result->getResultArray();

        return array_values(array_unique(array_filter(array_map(static fn (array $row): int => (int) ($row['user_id'] ?? 0), $rows))));
    }

    protected function userIdsForAssignedLaborans(int $proposalId): array
    {
        $db = db_connect();

        if (! $db) {
            return [];
        }

        $result = $db->query(
            'SELECT DISTINCT assignments.user_id AS user_id
            FROM laboratory_loan_proposal_items items
            INNER JOIN laboratory_laborans assignments
                ON assignments.laboratory_id = items.laboratory_id
            WHERE items.proposal_id = ?',
            [(int) $proposalId]
        );

        if (! $result || ! method_exists($result, 'getResultArray')) {
            return [];
        }

        $rows = $result->getResultArray();
        $userIds = array_map(static fn (array $row): int => (int) ($row['user_id'] ?? 0), $rows);
        $userIds = array_values(array_filter($userIds, static fn (int $userId): bool => $userId > 0));

        return array_values(array_unique($userIds));
    }

    protected function userIdsForAssetProposalReview(int $proposalId): array
    {
        $db = db_connect();

        if (! $db) {
            return [];
        }

        $result = $db->query(
            'SELECT DISTINCT assignments.user_id AS user_id
            FROM asset_loan_proposal_items items
            INNER JOIN assets ON assets.id = items.asset_id
            INNER JOIN laboratory_laborans assignments
                ON assignments.laboratory_id = assets.laboratory_id
            WHERE items.proposal_id = ?',
            [(int) $proposalId]
        );

        if (! $result || ! method_exists($result, 'getResultArray')) {
            return $this->userIdsByGroup('kepala_lab');
        }

        $rows = $result->getResultArray();
        $userIds = array_map(static fn (array $row): int => (int) ($row['user_id'] ?? 0), $rows);
        $userIds = array_values(array_filter($userIds, static fn (int $userId): bool => $userId > 0));

        $headLabIds = $this->userIdsByGroup('kepala_lab');

        return array_values(array_unique(array_merge($userIds, $headLabIds)));
    }
}
