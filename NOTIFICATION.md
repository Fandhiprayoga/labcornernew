# Modul Notifikasi

Dokumentasi library notifikasi generik yang dapat dipakai oleh semua modul untuk menyimpan dan membaca notifikasi user.

## Spesifikasi

### Struktur Tabel `notifications`

| Kolom        | Tipe               | Keterangan                                        |
|--------------|--------------------|----------------------------------------------------|
| `id`         | INT unsigned PK    | Auto increment                                      |
| `uuid`       | CHAR(36)           | Unique, digenerate otomatis saat insert             |
| `user_id`    | INT unsigned       | FK ke `users.id` (penerima notifikasi)              |
| `module`     | VARCHAR(50) null   | Nama modul asal notifikasi, mis. `assets`, `rooms`  |
| `type`       | VARCHAR(20)        | `info` (default), `success`, `warning`, `danger`    |
| `title`      | VARCHAR(150)       | Judul notifikasi                                    |
| `message`    | TEXT null          | Isi pesan                                           |
| `data`       | TEXT null          | Payload tambahan, disimpan sebagai JSON              |
| `url`        | VARCHAR(255) null  | Link tujuan saat notifikasi diklik                  |
| `is_read`    | TINYINT(1)         | 0 = belum dibaca, 1 = sudah dibaca                  |
| `read_at`    | DATETIME null      | Waktu dibaca                                        |
| `created_at` / `updated_at` / `deleted_at` | DATETIME null | Timestamp & soft delete |

Migration: [app/Database/Migrations/2026-08-31-000001_CreateNotificationsTable.php](app/Database/Migrations/2026-08-31-000001_CreateNotificationsTable.php)
Model: [app/Models/NotificationModel.php](app/Models/NotificationModel.php)
Library: [app/Libraries/Notification.php](app/Libraries/Notification.php)

### Akses Library

Library terdaftar sebagai service `notification` di [app/Config/Services.php](app/Config/Services.php) dan tersedia lewat helper global `notification()` yang didefinisikan di [app/Common.php](app/Common.php). Helper ini bisa dipanggil dari controller, model, atau view manapun tanpa import tambahan.

## Cara Penggunaan

### 1. Mengirim notifikasi ke satu user

```php
notification()->send(
    userId: $user->id,
    title: 'Asset Dipinjam',
    message: 'Asset "Proyektor A" telah dipinjam.',
    options: [
        'module' => 'assets',
        'type'   => 'info',       // info | success | warning | danger
        'url'    => site_url('admin/assets/edit/' . $asset['uuid']),
        'data'   => ['asset_id' => $asset['id']], // opsional, disimpan sebagai JSON
    ]
);
```

### 2. Mengirim notifikasi ke banyak user sekaligus

```php
notification()->sendToMany(
    userIds: [1, 2, 3],
    title: 'Maintenance Terjadwal',
    message: 'Laboratorium akan maintenance besok.',
    options: ['module' => 'laboratories', 'type' => 'warning']
);
```

### 3. Mengirim notifikasi ke seluruh user pada suatu role/group

```php
notification()->sendToRole(
    group: 'laboran',
    title: 'Tugas Baru',
    message: 'Ada penugasan laboran baru yang perlu ditinjau.',
    options: ['module' => 'laboratory-laborans']
);
```

### 4. Mengambil daftar notifikasi milik user

```php
$notifications = notification()->getForUser($user->id, limit: 10, unreadOnly: false);
```

### 5. Menghitung notifikasi yang belum dibaca

```php
$unread = notification()->unreadCount($user->id);
```

### 6. Menandai sudah dibaca

```php
// Satu notifikasi (opsional validasi kepemilikan lewat $userId)
notification()->markAsRead($notificationId, $user->id);

// Semua notifikasi milik user
notification()->markAllAsRead($user->id);
```

### 7. Menghapus notifikasi

```php
notification()->delete($notificationId);
notification()->deleteAllForUser($user->id);
```

## Daftar Method Library

| Method | Return | Keterangan |
|--------|--------|------------|
| `send(int $userId, string $title, string $message = '', array $options = [])` | `array` | Simpan satu notifikasi, kembalikan row yang dibuat |
| `sendToMany(array $userIds, string $title, string $message = '', array $options = [])` | `void` | Kirim notifikasi yang sama ke banyak user |
| `sendToRole(string $group, string $title, string $message = '', array $options = [])` | `void` | Kirim ke semua user pada suatu Shield group/role |
| `getForUser(int $userId, int $limit = 10, bool $unreadOnly = false)` | `array` | Daftar notifikasi milik user, urut terbaru dulu |
| `unreadCount(int $userId)` | `int` | Jumlah notifikasi belum dibaca |
| `markAsRead(int $id, ?int $userId = null)` | `bool` | Tandai satu notifikasi dibaca |
| `markAllAsRead(int $userId)` | `bool` | Tandai semua notifikasi user dibaca |
| `delete(int $id)` | `bool` | Hapus satu notifikasi |
| `deleteAllForUser(int $userId)` | `bool` | Hapus semua notifikasi milik user |

`$options` yang didukung pada `send()` / `sendToMany()` / `sendToRole()`:

- `module` (string, opsional) — nama modul pengirim, mis. `assets`, `rooms`, `laboratories`.
- `type` (string, opsional, default `info`) — memengaruhi warna badge di UI: `info`, `success`, `warning`, `danger`.
- `url` (string, opsional) — link tujuan ketika notifikasi diklik/dibaca.
- `data` (array, opsional) — payload tambahan bebas, disimpan sebagai JSON di kolom `data`.

## UI yang Tersedia

- **Halaman daftar notifikasi**: `GET /notifications` — [app/Controllers/NotificationController.php](app/Controllers/NotificationController.php), view [app/Views/notifications/index.php](app/Views/notifications/index.php). Menampilkan seluruh notifikasi user, tombol "Tandai Semua Dibaca", dan hapus per item.
- **Menu sidebar**: menu "Notifikasi" pada grup "Akun" di bawah "Profil Saya" ([app/Views/partials/sidebar.php](app/Views/partials/sidebar.php)), menampilkan badge jumlah belum dibaca.
- **Dropdown navbar**: ikon lonceng di navbar ([app/Views/partials/navbar.php](app/Views/partials/navbar.php)) menampilkan 5 notifikasi terbaru beserta badge unread, dengan link ke halaman notifikasi lengkap.

### Route

| Method | Route | Aksi |
|--------|-------|------|
| GET | `/notifications` | Daftar notifikasi milik user |
| GET | `/notifications/read/(:num)` | Tandai satu notifikasi dibaca lalu redirect ke `url`-nya |
| POST | `/notifications/mark-all-read` | Tandai semua notifikasi dibaca |
| POST | `/notifications/delete/(:num)` | Hapus satu notifikasi |

Semua route berada dalam grup filter `session`, sehingga hanya bisa diakses oleh user yang sudah login.

## Contoh Integrasi di Modul Lain

Panggil `notification()->send(...)` setelah aksi penting di controller modul lain, misalnya setelah asset dipinjam:

```php
// di dalam AssetController atau service peminjaman
notification()->send(
    $peminjam->id,
    'Peminjaman Disetujui',
    "Peminjaman asset \"{$asset['name']}\" telah disetujui.",
    [
        'module' => 'assets',
        'type'   => 'success',
        'url'    => site_url('admin/assets/edit/' . $asset['uuid']),
    ]
);
```

Tidak perlu import namespace apa pun karena `notification()` adalah helper global yang otomatis tersedia di seluruh aplikasi.
