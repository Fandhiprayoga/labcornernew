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

        // Faculty management
        'faculties.list'      => 'Dapat melihat daftar fakultas',
        'faculties.create'    => 'Dapat membuat fakultas baru',
        'faculties.edit'      => 'Dapat mengedit fakultas',
        'faculties.delete'    => 'Dapat menghapus fakultas',

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
            'faculties.*',
            'dashboard.*',
            'reports.*',
        ],
        'kepala_lab' => [
            'admin.access',
            'users.create',
            'users.edit',
            'users.delete',
            'users.list',
            'users.manage-roles',
            'rooms.*',
            'faculties.*',
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
            'faculties.list',
        ],
        'asisten_lab' => [
            'dashboard.access',
            'dashboard.stats',
            'reports.view',
        ],
        'user' => [
            'dashboard.access',
        ],
    ];
}
