<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Dapatkan user provider dari Shield
        $users = auth()->getProvider();

        /**
         * Daftar user default yang akan dibuat
         */
        $defaultUsers = [
            [
                'username' => 'superadmin',
                'email'    => 'superadmin@example.com',
                'password' => 'password123',
                'group'    => 'superadmin',
            ],
            [
                'username' => 'kepala.lab',
                'email'    => 'kepala.lab@example.com',
                'password' => 'password123',
                'group'    => 'kepala_lab',
            ],
            [
                'username' => 'laboran',
                'email'    => 'laboran@example.com',
                'password' => 'password123',
                'group'    => 'laboran',
            ],
            [
                'username' => 'asisten.lab',
                'email'    => 'asisten.lab@example.com',
                'password' => 'password123',
                'group'    => 'asisten_lab',
            ],
            [
                'username' => 'user',
                'email'    => 'user@example.com',
                'password' => 'password123',
                'group'    => 'user',
            ],
        ];

        foreach ($defaultUsers as $userData) {
            // Buat user entity
            $user = new User([
                'username' => $userData['username'],
                'email'    => $userData['email'],
                'password' => $userData['password'],
                'active'   => 1,
            ]);

            // Simpan user
            $users->save($user);

            // Ambil user yang baru dibuat
            $user = $users->findById($users->getInsertID());

            // Assign group/role
            $user->addGroup($userData['group']);

            echo "User '{$userData['username']}' created with role '{$userData['group']}'\n";
        }

        echo "\n=== Default Login Credentials ===\n";
        echo "Super Admin : superadmin@example.com / password123\n";
        echo "Kepala Lab  : kepala.lab@example.com / password123\n";
        echo "Laboran     : laboran@example.com / password123\n";
        echo "Asisten Lab : asisten.lab@example.com / password123\n";
        echo "User        : user@example.com / password123\n";
        echo "=================================\n";
    }
}
