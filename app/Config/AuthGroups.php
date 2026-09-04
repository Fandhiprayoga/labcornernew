<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Kontrol penuh terhadap seluruh sistem.',
        ],
        'kepala_lab' => [
            'title'       => 'Kepala Laboratorium',
            'description' => 'Penanggung jawab operasional dan pelaporan laboratorium.',
        ],
        'laboran' => [
            'title'       => 'Laboran',
            'description' => 'Petugas yang membantu operasional dan administrasi laboratorium.',
        ],
        'asisten_lab' => [
            'title'       => 'Asisten Laboratorium',
            'description' => 'Petugas pendamping kegiatan operasional laboratorium.',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'Pengguna yang mengajukan dan memantau layanan laboratorium.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     */
    public array $permissions = [
        // Admin area
        'admin.access'        => 'Dapat mengakses area admin',
        'admin.settings'      => 'Dapat mengakses pengaturan sistem',

        // User management
        'users.list'          => 'Dapat melihat daftar pengguna',
        'users.create'        => 'Dapat membuat pengguna baru',
        'users.edit'          => 'Dapat mengedit pengguna',
        'users.delete'        => 'Dapat menghapus pengguna',
        'users.manage-roles'  => 'Dapat mengatur role pengguna',

        // Room management
        'rooms.list'          => 'Dapat melihat daftar ruangan',
        'rooms.create'        => 'Dapat membuat ruangan baru',
        'rooms.edit'          => 'Dapat mengedit ruangan',
        'rooms.delete'        => 'Dapat menghapus ruangan',

        // Laboratory management
        'laboratories.list'   => 'Dapat melihat daftar laboratorium',
        'laboratories.create' => 'Dapat membuat laboratorium baru',
        'laboratories.edit'   => 'Dapat mengedit laboratorium',
        'laboratories.delete' => 'Dapat menghapus laboratorium',

        // Asset management
        'assets.list'         => 'Dapat melihat daftar asset',
        'assets.create'       => 'Dapat membuat asset baru',
        'assets.bulk-create'  => 'Dapat melakukan bulk insert asset',
        'assets.edit'         => 'Dapat mengedit asset',
        'assets.delete'       => 'Dapat menghapus asset',

        // Laboratory laboran assignment
        'laboratory-laborans.list'   => 'Dapat melihat daftar penugasan laboran',
        'laboratory-laborans.create' => 'Dapat menugaskan laboran ke laboratorium',
        'laboratory-laborans.edit'   => 'Dapat mengedit penugasan laboran',
        'laboratory-laborans.delete' => 'Dapat menghapus penugasan laboran',

        // Faculty management
        'faculties.list'      => 'Dapat melihat daftar fakultas',
        'faculties.create'    => 'Dapat membuat fakultas baru',
        'faculties.edit'      => 'Dapat mengedit fakultas',
        'faculties.delete'    => 'Dapat menghapus fakultas',

        // Study program management
        'study-programs.list'   => 'Dapat melihat daftar program studi',
        'study-programs.create' => 'Dapat membuat program studi baru',
        'study-programs.edit'   => 'Dapat mengedit program studi',
        'study-programs.delete' => 'Dapat menghapus program studi',

        // Laboratory loan proposals
        'loans.access'  => 'Dapat mengakses modul proposal peminjaman',
        'loans.list'    => 'Dapat melihat proposal peminjaman',
        'loans.create'  => 'Dapat membuat proposal peminjaman',
        'loans.edit'    => 'Dapat mengedit proposal peminjaman',
        'loans.delete'  => 'Dapat membatalkan proposal peminjaman',

        // Role management
        'roles.list'          => 'Dapat melihat daftar role',
        'roles.create'        => 'Dapat membuat role baru',
        'roles.edit'          => 'Dapat mengedit role',
        'roles.delete'        => 'Dapat menghapus role',

        // Dashboard
        'dashboard.access'    => 'Dapat mengakses dashboard',
        'dashboard.stats'     => 'Dapat melihat statistik',

        // Reports
        'reports.view'        => 'Dapat melihat laporan',
        'reports.export'      => 'Dapat mengekspor laporan',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     */
    public array $matrix = [
        'superadmin' => [
            'admin.*',
            'users.*',
            'roles.*',
            'rooms.*',
            'laboratories.*',
            'assets.*',
            'laboratory-laborans.*',
            'faculties.*',
            'study-programs.*',
            'loans.*',
            'dashboard.*',
            'reports.*',
            'loans.*',
        ],
        'kepala_lab' => [
            'admin.access',
            'users.create',
            'users.edit',
            'users.delete',
            'users.list',
            'users.manage-roles',
            'rooms.*',
            'laboratories.*',
            'assets.*',
            'laboratory-laborans.*',
            'faculties.*',
            'study-programs.*',
            'admin.settings',
            'dashboard.*',
            'reports.*',
        ],
        'laboran' => [
            'admin.access',
            'users.list',
            'dashboard.access',
            'dashboard.stats',
            'reports.view',
            'reports.export',
            'rooms.list',
            'laboratories.list',
            'assets.list',
            'assets.create',
            'assets.bulk-create',
            'assets.edit',
            'faculties.list',
            'study-programs.list',
            'loans.access',
            'loans.list',
            'loans.edit',
            'loans.delete',
        ],
        'asisten_lab' => [
            'dashboard.access',
            'dashboard.stats',
            'reports.view',
        ],
        'user' => [
            'dashboard.access',
            'loans.access',
            'loans.list',
            'loans.create',
            'loans.edit',
            'loans.delete',
        ],
    ];
}
