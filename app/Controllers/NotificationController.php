<?php

namespace App\Controllers;

class NotificationController extends BaseController
{
    public function index()
    {
        $user = auth()->user();

        $data = [
            'title'         => 'Notifikasi',
            'page_title'    => 'Notifikasi',
            'notifications' => notification()->getForUser($user->id, 50),
            'unreadCount'   => notification()->unreadCount($user->id),
        ];

        return $this->renderView('notifications/index', $data);
    }

    public function read($id)
    {
        $user = auth()->user();
        $notif = (new \App\Models\NotificationModel())->find($id);

        if (! $notif || (int) $notif['user_id'] !== $user->id) {
            return redirect()->to('/notifications')->with('error', 'Notifikasi tidak ditemukan.');
        }

        notification()->markAsRead((int) $id, $user->id);

        return redirect()->to(! empty($notif['url']) ? $notif['url'] : '/notifications');
    }

    public function markAllRead()
    {
        $user = auth()->user();

        notification()->markAllAsRead($user->id);

        return redirect()->to('/notifications')->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function delete($id)
    {
        $user = auth()->user();

        $notif = (new \App\Models\NotificationModel())->find($id);

        if (! $notif || (int) $notif['user_id'] !== $user->id) {
            return redirect()->to('/notifications')->with('error', 'Notifikasi tidak ditemukan.');
        }

        notification()->delete((int) $id);

        return redirect()->to('/notifications')->with('success', 'Notifikasi berhasil dihapus.');
    }
}
